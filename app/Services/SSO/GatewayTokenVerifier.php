<?php
// app/Services/SSO/GatewayTokenVerifier.php

namespace App\Services\SSO;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GatewayTokenVerifier
{
    /**
     * Verify and validate Gateway-issued SSO token
     */
    public function verify(string $token): array
    {
        try {
            $decoded = JWT::decode(
                $token,
                new Key(
                    Config::get('services.gateway.signing_key'),
                    'HS256'
                )
            );

            /**
             * IMPORTANT:
             * firebase/php-jwt returns stdClass
             * We MUST deep-cast to array
             */
            $payload = json_decode(
                json_encode($decoded, JSON_THROW_ON_ERROR),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            $this->assertValid($payload);

            return $payload;

        } catch (Throwable $e) {
            Log::warning('Finance SSO token rejected', [
                'reason' => $e->getMessage(),
                'issuer' => Config::get('services.gateway.issuer'),
                'audience' => Config::get('services.gateway.audience'),
            ]);

            abort(403, 'Invalid SSO token');
        }
    }

    /**
     * Validate Gateway JWT contract (STRICT)
     */
    protected function assertValid(array $payload): void
    {
        $now = time();

        // --- ISSUER ---
        if (($payload['iss'] ?? null) !== Config::get('services.gateway.issuer')) {
            throw new RuntimeException('Invalid issuer');
        }

        // --- AUDIENCE ---
        if (($payload['aud'] ?? null) !== Config::get('services.gateway.audience')) {
            throw new RuntimeException('Invalid audience');
        }

        // --- EXPIRATION ---
        if (($payload['exp'] ?? 0) < $now) {
            throw new RuntimeException('Token expired');
        }

        // --- TTL GUARD (ANTI-REPLAY) ---
        if (
            !isset($payload['iat'], $payload['exp']) ||
            ($payload['exp'] - $payload['iat']) > 60
        ) {
            throw new RuntimeException('Token TTL exceeded');
        }

        // --- MODULE CONTEXT ---
        if (($payload['ctx']['module'] ?? null) !== 'finance') {
            throw new RuntimeException('Invalid module context');
        }
    }
}

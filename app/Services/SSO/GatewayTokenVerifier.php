<?php
// app/Services/SSO/GatewayTokenVerifier.php
namespace App\Services\SSO;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Exception;

class GatewayTokenVerifier
{
    public function verify(string $token): array
    {
        try {
            $decoded = JWT::decode(
                $token,
                new Key(Config::get('services.gateway.signing_key'), 'HS256')
            );

            $payload = (array) $decoded;

            $this->assertValid($payload);

            return $payload;

        } catch (Exception $e) {
            Log::warning('Invalid gateway SSO token', [
                'error' => $e->getMessage(),
            ]);

            abort(403, 'Invalid SSO token');
        }
    }

    protected function assertValid(array $payload): void
    {
        $now = time();

        if (($payload['iss'] ?? null) !== config('services.gateway.issuer')) {
            throw new Exception('Invalid issuer');
        }

        if (($payload['aud'] ?? null) !== config('services.gateway.audience')) {
            throw new Exception('Invalid audience');
        }

        if (($payload['exp'] ?? 0) < $now) {
            throw new Exception('Token expired');
        }

        if ((($payload['exp'] ?? 0) - ($payload['iat'] ?? 0)) > 60) {
            throw new Exception('Token TTL exceeded');
        }

        if (($payload['ctx']['module'] ?? null) !== 'finance') {
            throw new Exception('Invalid module context');
        }
    }
}

<?php
// finance app/Http/Controllers/SSO/ConsumeController.php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use App\Services\SSO\GatewayTokenVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConsumeController extends Controller
{
    /**
     * Entry point SSO from Reltroner Gateway
     * - Validates RMAT (JWT)
     * - Establishes finance-local session
     * - NO redirect back to Gateway here
     */
    public function consume(Request $request, GatewayTokenVerifier $verifier)
    {
        // 🔒 Enforce GET only (explicit)
        abort_if(!$request->isMethod('get'), 405);

        $token = $request->query('token');

        abort_if(!$token, 400, 'Missing SSO token.');

        try {
            // 🔐 Verify & decode RMAT token
            $payload = $verifier->verify($token);
        } catch (\Throwable $e) {
            Log::warning('Finance SSO token rejected', [
                'ip'    => $request->ip(),
                'error' => $e->getMessage(),
            ]);

            // ❌ Invalid token → force login via Gateway
            return redirect()->away(
                config('services.gateway.login_url')
            );
        }

        /**
         * 🔒 Security:
         * Regenerate session ONLY after token verified
         * (prevents fixation, keeps state clean)
         */
        $request->session()->regenerate();

        // ✅ Finance-local auth contract
        session([
            'finance_authenticated' => true,
            'finance_user' => [
                'external_id' => $payload['sub'] ?? null,
                'email'       => $payload['email'] ?? null,
            ],
            'gateway_issuer' => $payload['iss'] ?? null,
            'gateway_jti'    => $payload['jti'] ?? null,
        ]);

        // 🧾 Audit log (security channel if available)
        Log::info('Finance SSO session established', [
            'external_id' => $payload['sub'] ?? null,
            'issuer'      => $payload['iss'] ?? null,
            'jti'         => $payload['jti'] ?? null,
            'ip'          => $request->ip(),
            'session_id'  => session()->getId(),
        ]);

        // 🚀 Enter finance dashboard
        return redirect()->route('dashboard.index');
    }
}

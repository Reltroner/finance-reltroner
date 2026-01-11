<?php
// finance app/Http/Middleware/EnsureGatewayAuthenticated.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // ✅ FIX: Added missing import

class EnsureGatewayAuthenticated
{
    /**
     * Finance access gatekeeper
     * - Allows SSO consume endpoint
     * - Enforces finance-local session
     * - Redirects unauthenticated users to Gateway login
     */
    public function handle(Request $request, Closure $next)
    {
        /**
         * 🔓 Allow SSO consume endpoint unconditionally
         * (ONE-TIME entry from Gateway)
         */
        if ($request->routeIs('sso.consume')) {
            return $next($request);
        }

        /**
         * ✅ Finance-local session established
         */
        if (session('finance_authenticated') === true) {
            return $next($request);
        }

        Log::debug('Finance session check', [
            'session_id' => session()->getId(),
            'finance_authenticated' => session('finance_authenticated'),
            'cookies' => request()->cookies->all(),
        ]);

        /**
         * ❌ Not authenticated → redirect to Gateway login
         * NEVER redirect to dashboard or internal route
         */
        return redirect()->away(
            config('services.gateway.login_url')
        );
    }
}

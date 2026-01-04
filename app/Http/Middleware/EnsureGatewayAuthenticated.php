<?php
// app/Http/Middleware/EnsureGatewayAuthenticated.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureGatewayAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('sso/consume')) {
        return $next($request);
        }

        if (!session('finance_authenticated')) {
            return redirect(config('services.gateway.login_url'));
        }

        return $next($request);
    }
}

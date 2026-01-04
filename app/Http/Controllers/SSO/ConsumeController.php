<?php
// app/Http/Controllers/SSO/ConsumeController.php
namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use App\Services\SSO\GatewayTokenVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConsumeController extends Controller
{
    public function consume(Request $request, GatewayTokenVerifier $verifier)
    {
        if (!$request->isMethod('get')) {
            abort(405);
        }

        $token = $request->query('token');

        if (!$token) {
            abort(400, 'Missing SSO token');
        }

        try {
            $payload = $verifier->verify($token);
        } catch (\Throwable $e) {
            return redirect(config('services.gateway.login_url'));
        }

        $request->session()->regenerate();

        session([
            'finance_authenticated' => true,
            'external_id' => $payload['sub'],
            'email' => $payload['email'] ?? null,
            'issued_by' => $payload['iss'],
        ]);

        \Log::channel('security')->info('Finance SSO success', [
            'external_id' => $payload['sub'],
            'jti' => $payload['jti'] ?? null,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('dashboard.index');
    }

}

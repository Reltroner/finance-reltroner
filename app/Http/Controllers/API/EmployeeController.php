<?php
namespace App\Http\Controllers\API;
// http://finance.reltroner.local app/Http/Controllers/API/EmployeeController.php
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $base = config('services.modules.hrm') ?? env('HRM_SERVICE', 'http://hrm.reltroner.local:8080');

        $candidates = [
            rtrim($base, '/') . '/api/employees',        // common
            rtrim($base, '/') . '/employees',            // hrm web route found
            rtrim($base, '/') . '/api/public-employees' // route from route:list
        ];

        foreach ($candidates as $endpoint) {
            try {
                $resp = Http::withHeaders(['Accept' => 'application/json'])->timeout(8)->get($endpoint);

                if ($resp->successful()) {
                    // If it's HTML, try to parse JSON inside; but assume JSON
                    return response()->json($resp->json());
                }

                // log non-success for debugging
                Log::warning('EmployeeController: non-success', [
                    'endpoint' => $endpoint,
                    'status' => $resp->status(),
                    'body' => substr($resp->body(), 0, 1000),
                ]);
            } catch (\Throwable $e) {
                Log::warning('EmployeeController: exception', [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'error' => true,
            'message' => 'All HRM employee endpoints failed. Check HRM routes and logs.'
        ], 502);
    }
}

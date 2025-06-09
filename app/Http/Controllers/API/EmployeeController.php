<?php
// http://finance.reltroner.local app/Http/Controllers/API/EmployeeController.php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmployeeController extends Controller
{
    public function index()
    {
        $gatewayUrl = config('services.modules.gateway') . '/hrm/employees';

        $response = Http::get($gatewayUrl);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json([
            'message' => 'Failed to fetch employee data from gateway.',
            'error' => $response->body()
        ], $response->status());
    }
}

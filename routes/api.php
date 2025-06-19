<?php
// http://finance.reltroner.local routes/api.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\DashboardFinanceController;

Route::middleware('api')->get('/ping', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/employees', [EmployeeController::class, 'index']);


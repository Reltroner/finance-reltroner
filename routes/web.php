<?php
// routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\BudgetCategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This module (finance-reltroner) is fully modular and self-contained.
| Each route is prefixed only by its resource, not by /finance/ 
| because the domain already reflects the module.
|
| e.g., http://finance.reltroner.local:9002/transactions
|
*/

// Homepage fallback (optional)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// UI dashboard (Blade-based)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// RESTful Resource Routes
Route::resource('/transactions', TransactionController::class)->names('transactions');
Route::resource('/accounts', AccountController::class)->names('accounts');
Route::resource('/invoices', InvoiceController::class)->names('invoices');
Route::resource('/budgets', BudgetCategoryController::class)->names('budgets');

// API Endpoint (Sample static finance data)
Route::get('/api/dashboard-summary', function () {
    return response()->json([
        'assets' => 120000,
        'liabilities' => 50000,
        'equity' => 70000,
        'profit' => [15000, 12000, 18000, 20000, 17000, 21000],
        'loss' => [2000, 1000, 3000, 2500, 1500, 1800],
    ]);
})->name('api.dashboard.summary');

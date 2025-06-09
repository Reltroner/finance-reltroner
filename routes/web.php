<?php
// http://finance.reltroner.local routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\TransactionController;
use App\Http\Controllers\Finance\DashboardController;
use App\Http\Controllers\Finance\AccountController;
use App\Http\Controllers\Finance\InvoiceController;
use App\Http\Controllers\Finance\BudgetCategoryController;

Route::get('/', function () {
    return view('welcome');
});

// Untuk dashboard UI (blade)
Route::get('/finance/dashboard/index', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('/finance/transactions', TransactionController::class);

Route::resource('/finance/accounts', AccountController::class);

Route::resource('/finance/invoices', InvoiceController::class);

Route::resource('/finance/budgets', BudgetCategoryController::class);

Route::get('/finance/dashboard', function () {
    return response()->json([
        'assets' => 120000,
        'liabilities' => 50000,
        'equity' => 70000,
        'profit' => [15000, 12000, 18000, 20000, 17000, 21000],
        'loss' => [2000, 1000, 3000, 2500, 1500, 1800],
    ]);
});

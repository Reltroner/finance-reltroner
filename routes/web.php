<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TaxApplicationController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionDetailController;
use App\Http\Controllers\VendorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This module (finance-reltroner) is fully modular and self-contained.
| Each route is prefixed only by its resource, not by /finance/
| because the domain already reflects the module.
| Example: http://finance.reltroner.local:9002/transactions
|
*/

// Homepage fallback
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// UI dashboard (Blade-based)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// RESTful Resource Routes (Blade & web forms)
Route::resources([
    'transactions'        => TransactionController::class,
    'accounts'            => AccountController::class,
    'attachments'         => AttachmentController::class,
    'auditlogs'          => AuditLogController::class,
    'budgets'             => BudgetController::class,
    'cost-centers'        => CostCenterController::class,
    'currencies'          => CurrencyController::class,
    'customers'           => CustomerController::class,
    'invoices'            => InvoiceController::class,
    'payments'            => PaymentController::class,
    'tax-applications'    => TaxApplicationController::class,
    'taxes'               => TaxController::class,
    'transaction-details' => TransactionDetailController::class,
    'vendors'             => VendorController::class,
]);

// Attachment download (direct file)
Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');

// API Endpoint (Sample static finance data)
Route::get('/api/dashboard-summary', function () {
    return response()->json([
        'assets'      => 120000,
        'liabilities' => 50000,
        'equity'      => 70000,
        'profit'      => [15000, 12000, 18000, 20000, 17000, 21000],
        'loss'        => [2000, 1000, 3000, 2500, 1500, 1800],
    ]);
})->name('api.dashboard.summary');

<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    AccountController,
    AttachmentController,
    AuditLogController,
    BudgetController,
    CostCenterController,
    CurrencyController,
    CustomerController,
    InvoiceController,
    PaymentController,
    TaxApplicationController,
    TaxController,
    TransactionController,
    TransactionDetailController,
    VendorController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Modul finance-reltroner (Blade UI)
| Domain/subdomain modul sudah mengindikasikan konteks finance.
*/

Route::redirect('/', '/dashboard');

Route::middleware(['web', 'auth']) // sesuaikan middleware auth/session kamu
    ->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // General Ledger (buku besar) — konsisten dgn TransactionController@ledger
    // GET /ledger?account_id=&date_from=&date_to=&cost_center_id=&reference=&status=
    Route::get('/ledger', [TransactionController::class, 'ledger'])->name('transactions.ledger');

    // Resource routes (Blade CRUD)
    Route::resources([
        'accounts'            => AccountController::class,
        'attachments'         => AttachmentController::class,
        'auditlogs'           => AuditLogController::class,
        'budgets'             => BudgetController::class,
        'costcenters'         => CostCenterController::class,
        'currencies'          => CurrencyController::class,
        'customers'           => CustomerController::class,
        'invoices'            => InvoiceController::class,
        'payments'            => PaymentController::class,
        'tax-applications'    => TaxApplicationController::class,
        'taxes'               => TaxController::class,
        'transaction-details' => TransactionDetailController::class,
        'transactions'        => TransactionController::class,
        'vendors'             => VendorController::class,
    ]);

    // Attachment download (file langsung)
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download');

    // Sample API untuk dashboard (opsional)
    Route::get('/api/dashboard-summary', function () {
        return response()->json([
            'assets'      => 120000,
            'liabilities' => 50000,
            'equity'      => 70000,
            'profit'      => [15000, 12000, 18000, 20000, 17000, 21000],
            'loss'        => [2000, 1000, 3000, 2500, 1500, 1800],
        ]);
    })->name('api.dashboard.summary');
});

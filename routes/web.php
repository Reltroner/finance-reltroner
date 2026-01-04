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

use App\Http\Controllers\SSO\ConsumeController;
use App\Http\Middleware\EnsureGatewayAuthenticated;

/*
|--------------------------------------------------------------------------
| Finance Reltroner — Web Routes
|--------------------------------------------------------------------------
| Context:
| - Subdomain: finance.reltroner.*
| - Auth handled ONLY via Reltroner Auth Gateway
| - No direct Keycloak interaction
| - No Laravel Auth::login()
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| SSO Entry Point (UNPROTECTED)
|--------------------------------------------------------------------------
| - One-time entry from Gateway
| - Validates signed RMAT token
| - Creates finance-local session
|--------------------------------------------------------------------------
*/
Route::get('/sso/consume', [ConsumeController::class, 'consume'])
    ->name('sso.consume');


/*
|--------------------------------------------------------------------------
| Root Redirect
|--------------------------------------------------------------------------
| - Finance has no public landing page
| - All access goes through /dashboard
|--------------------------------------------------------------------------
*/
Route::redirect('/', '/dashboard');


/*
|--------------------------------------------------------------------------
| Protected Finance Area
|--------------------------------------------------------------------------
| - Requires finance-local session
| - Enforced by EnsureGatewayAuthenticated
|--------------------------------------------------------------------------
*/
Route::middleware(['web', EnsureGatewayAuthenticated::class])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard.index');


        /*
        |--------------------------------------------------------------------------
        | General Ledger
        |--------------------------------------------------------------------------
        | GET /ledger?account_id=&date_from=&date_to=&cost_center_id=&reference=&status=
        */
        Route::get('/ledger', [TransactionController::class, 'ledger'])
            ->name('transactions.ledger');


        /*
        |--------------------------------------------------------------------------
        | Resource Routes (Blade CRUD)
        |--------------------------------------------------------------------------
        */
        Route::resources([
            'accounts'             => AccountController::class,
            'attachments'          => AttachmentController::class,
            'auditlogs'            => AuditLogController::class,
            'budgets'              => BudgetController::class,
            'costcenters'          => CostCenterController::class,
            'currencies'           => CurrencyController::class,
            'customers'            => CustomerController::class,
            'invoices'             => InvoiceController::class,
            'payments'             => PaymentController::class,
            'tax-applications'     => TaxApplicationController::class,
            'taxes'                => TaxController::class,
            'transaction-details'  => TransactionDetailController::class,
            'transactions'         => TransactionController::class,
            'vendors'              => VendorController::class,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Attachments
        |--------------------------------------------------------------------------
        */
        Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])
            ->name('attachments.download');


        /*
        |--------------------------------------------------------------------------
        | Internal Dashboard API (OPTIONAL)
        |--------------------------------------------------------------------------
        | - Digunakan oleh Blade / JS dashboard
        | - BUKAN public API
        |--------------------------------------------------------------------------
        */
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

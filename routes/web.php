<?php
// routes/web.php (Finance Module)

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
| Finance Reltroner — Web Routes (PHASE 3)
|--------------------------------------------------------------------------
| Context:
| - Module: Finance
| - Auth: Reltroner Gateway ONLY
| - No direct Keycloak usage
| - No Laravel Auth::login()
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| SSO ENTRY POINT (PUBLIC)
|--------------------------------------------------------------------------
| - Single-use entry from Gateway
| - Verifies RMAT (JWT)
| - Creates finance-local session
|--------------------------------------------------------------------------
*/
Route::get('/sso/consume', [ConsumeController::class, 'consume'])
    ->name('sso.consume');


/*
|--------------------------------------------------------------------------
| ROOT ACCESS
|--------------------------------------------------------------------------
| - Finance has NO public landing page
| - Root always resolves to dashboard
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('dashboard');
});


/*
|--------------------------------------------------------------------------
| PROTECTED FINANCE AREA
|--------------------------------------------------------------------------
| - Requires finance-local session
| - Enforced strictly by EnsureGatewayAuthenticated
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
            ->name('dashboard');

        Route::get('/dashboard/index', [DashboardController::class, 'index'])
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
        | Attachments Download
        |--------------------------------------------------------------------------
        */
        Route::get(
            'attachments/{attachment}/download',
            [AttachmentController::class, 'download']
        )->name('attachments.download');


        /*
        |--------------------------------------------------------------------------
        | Internal Dashboard API (NON-PUBLIC)
        |--------------------------------------------------------------------------
        | - For Blade / JS dashboard only
        | - NOT a public API
        |--------------------------------------------------------------------------
        */
        Route::get('/_internal/dashboard-summary', function () {
            return response()->json([
                'assets'      => 120000,
                'liabilities' => 50000,
                'equity'      => 70000,
                'profit'      => [15000, 12000, 18000, 20000, 17000, 21000],
                'loss'        => [2000, 1000, 3000, 2500, 1500, 1800],
            ]);
        })->name('internal.dashboard.summary');

    });

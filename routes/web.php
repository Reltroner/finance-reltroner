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

use App\Http\Controllers\Reports\{
    TrialBalanceController,
    ProfitLossController,
    BalanceSheetController,
    ComparativeProfitLossController,
    ComparativeBalanceSheetController
};

use App\Http\Controllers\SSO\ConsumeController;
use App\Http\Middleware\EnsureGatewayAuthenticated;

/*
|--------------------------------------------------------------------------
| Finance Reltroner — Web Routes
|--------------------------------------------------------------------------
| Module : Finance
| Auth   : Reltroner Gateway ONLY
| Policy : STEP 5.2 (Accounting Core) is FROZEN
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| SSO ENTRY POINT (PUBLIC)
|--------------------------------------------------------------------------
*/
Route::get('/sso/consume', [ConsumeController::class, 'consume'])
    ->name('sso.consume');


/*
|--------------------------------------------------------------------------
| ROOT ACCESS
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('dashboard'));


/*
|--------------------------------------------------------------------------
| PROTECTED FINANCE AREA
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


        /*
        |--------------------------------------------------------------------------
        | General Ledger (READ VIEW)
        |--------------------------------------------------------------------------
        */
        Route::get('/ledger', [TransactionController::class, 'ledger'])
            ->name('transactions.ledger');


        /*
        |--------------------------------------------------------------------------
        | CRUD — Master & Transaction Data
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
        Route::get(
            'attachments/{attachment}/download',
            [AttachmentController::class, 'download']
        )->name('attachments.download');


        /*
        |--------------------------------------------------------------------------
        | FINANCIAL REPORTS — READ ONLY (STEP 5.3+)
        |--------------------------------------------------------------------------
        | ❗ NO WRITE
        | ❗ NO MUTATION
        | ❗ NO ACCOUNTING LOGIC
        |--------------------------------------------------------------------------
        */
        Route::prefix('reports')->name('reports.')->group(function () {

            /*
            |------------------------------------------------------------------
            | Single-period Statements (5.3A / 5.3B)
            |------------------------------------------------------------------
            */
            Route::get(
                '/trial-balance/{fiscalPeriodId}',
                TrialBalanceController::class
            )->name('trial-balance');

            Route::get(
                '/profit-loss/{fiscalPeriodId}',
                ProfitLossController::class
            )->name('profit-loss');

            Route::get(
                '/balance-sheet/{fiscalPeriodId}',
                BalanceSheetController::class
            )->name('balance-sheet');


            /*
            |------------------------------------------------------------------
            | Comparative Statements (5.3C / 5.3D)
            |------------------------------------------------------------------
            */
            Route::get(
                '/profit-loss/comparative',
                ComparativeProfitLossController::class
            )->name('profit-loss.comparative');

            Route::get(
                '/balance-sheet/comparative',
                ComparativeBalanceSheetController::class
            )->name('balance-sheet.comparative');
        });


        /*
        |--------------------------------------------------------------------------
        | INTERNAL DASHBOARD API (READ ONLY)
        |--------------------------------------------------------------------------
        | ❌ Not Public API
        | ❌ No accounting mutation
        |--------------------------------------------------------------------------
        */
        Route::get(
            '/_internal/dashboard-summary',
            [DashboardController::class, 'summary']
        )->name('internal.dashboard.summary');

    });

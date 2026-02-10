<?php
// app/Http/Controllers/Reports/BalanceSheetController.php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Accounting\Read\Statements\BalanceSheetService;
use Illuminate\Http\Request;

class BalanceSheetController extends Controller
{
    /**
     * Display Balance Sheet for a given fiscal period.
     *
     * READ-ONLY controller.
     * No accounting logic is allowed here.
     */
    public function __invoke(
        Request $request,
        int $fiscalPeriodId,
        BalanceSheetService $service
    ) {
        return view('reports.balance-sheet', [
            'statement' => $service->generate($fiscalPeriodId),
            'fiscalPeriodId' => $fiscalPeriodId,
        ]);
    }
}

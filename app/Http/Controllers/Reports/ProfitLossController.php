<?php
// app/Http/Controllers/Reports/ProfitLossController.php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Accounting\Read\Statements\ProfitLossService;
use Illuminate\Http\Request;

class ProfitLossController extends Controller
{
    /**
     * Display Profit & Loss Statement for a given fiscal period.
     *
     * READ-ONLY controller.
     * All accounting logic is delegated to the read service layer.
     */
    public function __invoke(
        Request $request,
        int $fiscalPeriodId,
        ProfitLossService $service
    ) {
        return view('reports.profit-loss', [
            'statement' => $service->generate($fiscalPeriodId),
            'fiscalPeriodId' => $fiscalPeriodId,
        ]);
    }
}

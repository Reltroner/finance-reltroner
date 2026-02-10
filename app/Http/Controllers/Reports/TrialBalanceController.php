<?php
// app/Http/Controllers/Reports/TrialBalanceController.php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Accounting\Read\TrialBalanceService;

class TrialBalanceController extends Controller
{
    public function __invoke(
        int $fiscalPeriodId,
        TrialBalanceService $service
    ) {
        return view('reports.trial-balance', [
            'balances' => $service->generate($fiscalPeriodId)
        ]);
    }
}

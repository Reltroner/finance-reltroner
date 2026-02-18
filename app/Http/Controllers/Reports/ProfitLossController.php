<?php
// app/Http/Controllers/Reports/ProfitLossController.php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\FiscalPeriod;
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
        $period = FiscalPeriod::findOrFail($fiscalPeriodId);

        $statement = $service->generate(
            $period->year,
            $period->period
        );

        return view('reports.profit-loss', [
            'statement'      => $statement,
            'fiscalPeriodId' => $fiscalPeriodId,
        ]);
    }
}

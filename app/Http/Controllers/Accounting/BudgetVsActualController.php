<?php
// app/Http/Controllers/Accounting/BudgetVsActualController.php
declare(strict_types=1);

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\Budget\Reporting\BudgetVsActualReportService;
use Illuminate\Contracts\View\View;

final class BudgetVsActualController extends Controller
{
    public function __construct(
        private readonly BudgetVsActualReportService $reportService
    ) {}

    /**
     * STEP 5.5 — Read-Only Boundary
     *
     * ❗ No accounting logic here
     * ❗ No mutation
     * ❗ No ledger access
     * ❗ No budget modification
     *
     * This controller acts purely as HTTP adapter.
     */
    public function show(int $periodId, int $version): View
    {
        $report = $this->reportService
            ->generate($periodId, $version);

        return view(
            'reports.budget-vs-actual',
            [
                'report'  => $report,
                'periodId'=> $periodId,
                'version' => $version,
            ]
        );
    }
}
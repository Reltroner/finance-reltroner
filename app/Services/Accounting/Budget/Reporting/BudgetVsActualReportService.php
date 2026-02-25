<?php
// app/Services/Accounting/Budget/Reporting/BudgetVsActualReportService.php
declare(strict_types=1);

namespace App\Services\Accounting\Budget\Reporting;

use App\Services\Accounting\Budget\BudgetVsActualService;
use App\Services\Accounting\Budget\Reporting\DTO\BudgetVsActualReportDTO;
use DomainException;

final class BudgetVsActualReportService
{
    public function __construct(
        private readonly BudgetVsActualService $engine
    ) {}

    public function generate(int $fiscalPeriodId, int $version): BudgetVsActualReportDTO
    {
        $rows = $this->engine->compare($fiscalPeriodId, $version);

        if (empty($rows)) {
            throw new DomainException('No comparison data found.');
        }

        $totalActual = 0.0;
        $totalBudget = 0.0;
        $totalVariance = 0.0;

        foreach ($rows as $row) {
            $totalActual += $row->actual;
            $totalBudget += $row->budget;
            $totalVariance += $row->variance;
        }

        if ($totalBudget === 0.0) {
            throw new DomainException('Total budget is zero.');
        }

        $totalVariancePercent = $totalVariance / $totalBudget;

        return new BudgetVsActualReportDTO(
            rows: $rows,
            totalActual: round($totalActual, 4),
            totalBudget: round($totalBudget, 4),
            totalVariance: round($totalVariance, 4),
            totalVariancePercent: round($totalVariancePercent, 4),
        );
    }
}
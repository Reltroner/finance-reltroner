<?php
// app/Services/Accounting/Budget/Reporting/DTO/BudgetVsActualReportDTO.php
declare(strict_types=1);

namespace App\Services\Accounting\Budget\Reporting\DTO;

final class BudgetVsActualReportDTO
{
    public function __construct(
        public readonly array $rows,
        public readonly float $totalActual,
        public readonly float $totalBudget,
        public readonly float $totalVariance,
        public readonly float $totalVariancePercent,
    ) {}
}
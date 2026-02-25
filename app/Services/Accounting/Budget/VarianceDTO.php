<?php
// app/Services/Accounting/Budget/VarianceDTO.php
declare(strict_types=1);

namespace App\Services\Accounting\Budget;

final class VarianceDTO
{
    public function __construct(
        public readonly string $metric,
        public readonly string $period,
        public readonly float $actual,
        public readonly float $budget,
        public readonly float $absoluteVariance,
        public readonly float $percentageVariance
    ) {}
}
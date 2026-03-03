<?php
// app/Services/Accounting/Hybrid/ForecastVsBudgetDTO.php
namespace App\Services\Accounting\Hybrid;

final class ForecastVsBudgetDTO
{
    public function __construct(
        public readonly string $metric,
        public readonly array $periods,
        public readonly array $forecastValues,
        public readonly array $budgetValues,
        public readonly array $variance,
        public readonly array $variancePercent
    ) {}
}
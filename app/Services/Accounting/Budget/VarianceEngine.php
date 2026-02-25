<?php
// app/Services/Accounting/Budget/VarianceEngine.php
declare(strict_types=1);

namespace App\Services\Accounting\Budget;

use DomainException;

final class VarianceEngine
{
    public static function compute(
        string $metric,
        string $period,
        float $actual,
        float $budget
    ): VarianceDTO {

        if ($metric === '') {
            throw new DomainException('Metric cannot be empty.');
        }

        if ($period === '') {
            throw new DomainException('Period cannot be empty.');
        }

        if ($budget == 0.0) {
            throw new DomainException('Budget cannot be zero.');
        }

        if (!is_finite($actual) || !is_finite($budget)) {
            throw new DomainException('Invalid numeric input.');
        }

        // raw computation (no rounding yet)
        $absoluteRaw = $actual - $budget;

        // rounding policy
        $absolute = round($absoluteRaw, 4);

        $percentageRaw = ($absoluteRaw / $budget) * 100;
        $percentage = round($percentageRaw, 4);

        return new VarianceDTO(
            metric: $metric,
            period: $period,
            actual: round($actual, 4),
            budget: round($budget, 4),
            absoluteVariance: $absolute,
            percentageVariance: $percentage
        );
    }
}
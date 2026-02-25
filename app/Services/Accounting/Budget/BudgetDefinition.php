<?php
// app/Services/Accounting/Budget/BudgetDefinition.php
declare(strict_types=1);

namespace App\Services\Accounting\Budget;

use DomainException;

final class BudgetDefinition
{
    public readonly string $snapshotVersion;
    public readonly int $budgetVersion;
    public readonly string $metric;
    public readonly string $period;
    public readonly float $plannedValue;

    public function __construct(
        string $snapshotVersion,
        int $budgetVersion,
        string $metric,
        string $period,
        float $plannedValue
    ) {
        if (trim($snapshotVersion) === '') {
            throw new DomainException('Snapshot version cannot be empty.');
        }

        if ($budgetVersion < 1) {
            throw new DomainException('Budget version must be >= 1.');
        }

        if (trim($metric) === '') {
            throw new DomainException('Metric cannot be empty.');
        }

        if (trim($period) === '') {
            throw new DomainException('Period cannot be empty.');
        }

        if ($plannedValue < 0) {
            throw new DomainException('Planned value cannot be negative.');
        }

        $this->snapshotVersion = $snapshotVersion;
        $this->budgetVersion = $budgetVersion;
        $this->metric = $metric;
        $this->period = $period;
        $this->plannedValue = round($plannedValue, 4);
    }
}
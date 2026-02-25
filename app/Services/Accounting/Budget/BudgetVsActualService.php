<?php
// app/Services/Accounting/Budget/BudgetVsActualService.php
declare(strict_types=1);

namespace App\Services\Accounting\Budget;

use App\Services\Accounting\Budget\Contracts\BudgetRepositoryInterface;
use App\Services\Accounting\Budget\Contracts\SnapshotPayloadReader;
use DomainException;

final class BudgetVsActualService
{
    public function __construct(
        private readonly SnapshotPayloadReader $snapshotAdapter,
        private readonly BudgetRepositoryInterface $budgetRepository
    ) {}

    public function compare(int $fiscalPeriodId, int $version): array
    {
        $snapshot = $this->snapshotAdapter
            ->getPayload($fiscalPeriodId, $version);

        $budgets = $this->budgetRepository
            ->getBySnapshotAndVersion($fiscalPeriodId, $version);

        if ($budgets->isEmpty()) {
            throw new DomainException('Budget not found.');
        }

        $result = [];

        foreach ($snapshot as $metric => $periods) {

            // Validate metric exists
            if (!$budgets->contains(fn ($b) => $b->metric === $metric)) {
                throw new DomainException("Missing metric in budget: {$metric}");
            }

            foreach ($periods as $period => $actual) {

                $budgetRow = $budgets->first(
                    fn ($b) => $b->metric === $metric && $b->period === $period
                );

                // Validate period exists
                if (!$budgetRow) {
                    throw new DomainException(
                        "Missing period {$period} for metric {$metric}"
                    );
                }

                $budgetAmount = (float) $budgetRow->amount;
                $actualAmount = (float) $actual;

                if ($budgetAmount === 0.0) {
                    throw new DomainException(
                        "Budget is zero for metric {$metric} period {$period}"
                    );
                }

                $variance = $actualAmount - $budgetAmount;
                $variancePercent = $variance / $budgetAmount;

                $result[] = (object) [
                    'metric'           => $metric,
                    'period'           => $period,
                    'actual'           => round($actualAmount, 4),
                    'budget'           => round($budgetAmount, 4),
                    'variance'         => round($variance, 4),
                    'variance_percent' => round($variancePercent, 4),
                ];
            }
        }

        // Explicit deterministic sort
        usort($result, function ($a, $b) {
            return [$a->period, $a->metric]
                <=> [$b->period, $b->metric];
        });

        return $result;
    }
}
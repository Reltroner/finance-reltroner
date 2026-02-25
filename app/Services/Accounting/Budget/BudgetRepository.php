<?php
// app/Services/Accounting/Budget/BudgetRepository.php
declare(strict_types=1);

namespace App\Services\Accounting\Budget;

use App\Models\FinancialBudget;
use App\Services\Accounting\Snapshot\SnapshotQueryService;
use App\Services\Accounting\Snapshot\Contracts\SnapshotExistenceChecker;
use App\Services\Accounting\Budget\Contracts\BudgetRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use DomainException;

final class BudgetRepository implements BudgetRepositoryInterface
{
    public function __construct(
        private readonly SnapshotExistenceChecker $snapshotQueryService
    ) {}

    /**
     * Store a batch of BudgetDefinition (append-only).
     *
     * @param Collection<int, BudgetDefinition> $budgets
     */
    public function storeBatch(Collection $budgets): void
    {
        if ($budgets->isEmpty()) {
            throw new DomainException('Budget batch cannot be empty.');
        }

        /** @var BudgetDefinition $first */
        $first = $budgets->first();

        $snapshotVersion = $first->snapshotVersion;
        $budgetVersion   = $first->budgetVersion;

        // Snapshot existence validation (read-only)
        if (! $this->snapshotQueryService->exists($snapshotVersion)) {
            throw new DomainException('Snapshot version does not exist.');
        }

        foreach ($budgets as $budget) {

            if (! $budget instanceof BudgetDefinition) {
                throw new DomainException('Invalid budget object.');
            }

            if ($budget->snapshotVersion !== $snapshotVersion) {
                throw new DomainException('Mixed snapshot version in batch.');
            }

            if ($budget->budgetVersion !== $budgetVersion) {
                throw new DomainException('Mixed budget version in batch.');
            }

            FinancialBudget::create([
                'id'               => (string) Str::uuid(),
                'snapshot_version' => $budget->snapshotVersion,
                'budget_version'   => $budget->budgetVersion,
                'metric'           => $budget->metric,
                'period'           => $budget->period,
                'planned_value'    => $budget->plannedValue,
                'created_at'       => now(),
            ]);
        }
    }

    /**
     * Retrieve budgets deterministically.
     *
     * @return Collection<int, BudgetDefinition>
     */
    public function getBySnapshotAndVersion(
        int $fiscalPeriodId,
        int $version
    ): Collection {

        if (trim($snapshotVersion) === '') {
            throw new DomainException('Snapshot version cannot be empty.');
        }

        if ($budgetVersion < 1) {
            throw new DomainException('Budget version must be >= 1.');
        }

        $rows = FinancialBudget::query()
            ->where('fiscal_period_id', $fiscalPeriodId)
            ->where('version', $version)
            ->orderBy('period')
            ->orderBy('metric')
            ->orderBy('budget_version')
            ->get();

        return $rows->map(function ($row) {
            return new BudgetDefinition(
                snapshotVersion: $row->snapshot_version,
                budgetVersion:   $row->budget_version,
                metric:          $row->metric,
                period:          $row->period,
                plannedValue:    (float) $row->planned_value
            );
        });
    }
}
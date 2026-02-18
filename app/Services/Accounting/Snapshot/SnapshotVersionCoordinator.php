<?php
// app/Services/Accounting/Snapshot/SnapshotVersionCoordinator.php
namespace App\Services\Accounting\Snapshot;

use Illuminate\Support\Facades\DB;

final class SnapshotVersionCoordinator
{
    /**
     * Determine next monotonic version for a fiscal period.
     *
     * Does NOT perform persistence.
     * Does NOT open transaction.
     * Does NOT perform retry.
     */
    public function nextVersion(int $fiscalPeriodId): int
    {
        $current = DB::table('financial_snapshots')
            ->where('fiscal_period_id', $fiscalPeriodId)
            ->max('version');

        return $current ? $current + 1 : 1;
    }
}

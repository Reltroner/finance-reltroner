<?php
// tests/Unit/Accounting/Snapshot/LedgerDriftDetectionTest.php

namespace Tests\Unit\Accounting\Snapshot;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\FiscalPeriod;
use App\Services\Accounting\Snapshot\MultiStatementSnapshotService;
use App\Services\Accounting\Snapshot\LedgerStateHasher;
use DomainException;

class LedgerDriftDetectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_ledger_drift_is_detected_and_blocks_snapshot(): void
    {
        $period = FiscalPeriod::factory()->create();
        $periodId = $period->id;

        // Mock LedgerStateHasher to simulate drift
        $this->app->bind(LedgerStateHasher::class, function () {
            return new class extends LedgerStateHasher {

                private bool $firstCall = true;

                public function hashForFiscalPeriod(int $year, int $period): string
                {
                    if ($this->firstCall) {
                        $this->firstCall = false;
                        return 'hash_before';
                    }

                    return 'hash_after'; // simulate ledger drift
                }
            };
        });

        $service = app(MultiStatementSnapshotService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Ledger drift');

        $service->generate($periodId);

        // Ensure rollback happened
        $count = DB::table('financial_snapshots')->count();

        $this->assertEquals(
            0,
            $count,
            'Snapshot persisted despite ledger drift detection.'
        );
    }
}

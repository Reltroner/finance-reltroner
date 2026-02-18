<?php
// tests/Unit/Accounting/Snapshot/MultiStatementAtomicityTest.php

namespace Tests\Unit\Accounting\Snapshot;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\FiscalPeriod;
use App\Services\Accounting\Snapshot\MultiStatementSnapshotService;
use App\Services\Accounting\Snapshot\LedgerStateHasher;
use DomainException;

class MultiStatementAtomicityTest extends TestCase
{
    use RefreshDatabase;

    public function test_ledger_drift_causes_full_rollback(): void
    {
        $period = FiscalPeriod::factory()->create();
        $periodId = $period->id;

        // Bind malicious hasher that changes hash after first call
        $this->app->bind(LedgerStateHasher::class, function () {
            return new class extends LedgerStateHasher {

                private bool $called = false;

                public function hashForFiscalPeriod(int $year, int $period): string
                {
                    if (!$this->called) {
                        $this->called = true;
                        return 'hash_before';
                    }

                    return 'hash_after'; // simulate ledger drift
                }
            };
        });

        $service = app(MultiStatementSnapshotService::class);

        try {
            $service->generate($periodId);
            $this->fail('Ledger drift was not detected.');
        } catch (DomainException $e) {
            $this->assertStringContainsString(
                'drift',
                strtolower($e->getMessage())
            );
        }

        $count = DB::table('financial_snapshots')->count();

        $this->assertEquals(
            0,
            $count,
            'Snapshot persisted despite ledger drift.'
        );
    }
}

<?php
// tests/Unit/Accounting/Snapshot/SnapshotAtomicityTest.php
namespace Tests\Unit\Accounting\Snapshot;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\FiscalPeriod;
use App\Services\Accounting\Snapshot\SnapshotGenerationService;
use App\Services\Accounting\Snapshot\LedgerStateHasher;
use RuntimeException;

class SnapshotAtomicityTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_generation_rolls_back_when_hasher_fails(): void
    {
        $period = FiscalPeriod::factory()->create();
        $periodId = $period->id;

        // Replace LedgerStateHasher with failing stub
        $this->app->bind(LedgerStateHasher::class, function () {
            return new class extends LedgerStateHasher {
                public function hashForFiscalPeriod(int $year, int $period): string
                {
                    throw new RuntimeException('Simulated hasher failure.');
                }
            };
        });

        $service = app(SnapshotGenerationService::class);

        $beforeCount = DB::table('financial_snapshots')->count();

        try {
            $service->generate($periodId);
            $this->fail('Exception was not thrown.');
        } catch (RuntimeException $e) {
            $this->assertEquals('Simulated hasher failure.', $e->getMessage());
        }

        $afterCount = DB::table('financial_snapshots')->count();

        $this->assertEquals(
            $beforeCount,
            $afterCount,
            'Atomicity violated: snapshot row was inserted despite failure.'
        );
    }
}

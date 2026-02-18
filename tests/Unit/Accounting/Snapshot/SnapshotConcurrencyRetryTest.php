<?php
// tests/Unit/Accounting/Snapshot/SnapshotConcurrencyRetryTest.php
namespace Tests\Unit\Accounting\Snapshot;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\FiscalPeriod;
use App\Services\Accounting\Snapshot\SnapshotGenerationService;

class SnapshotConcurrencyRetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_retry_on_version_conflict(): void
    {
        $period = FiscalPeriod::factory()->create();
        $periodId = $period->id;

        $service = app(SnapshotGenerationService::class);

        // Simulate concurrent insert by manually inserting version 1
        DB::table('financial_snapshots')->insert([
            'id' => 'manual-id',
            'fiscal_period_id' => $periodId,
            'version' => 1,
            'payload_json' => '{}',
            'payload_hash' => hash('sha256', '{}'),
            'source_hash' => hash('sha256', 'test'),
            'created_at' => now(),
        ]);

        $snapshot = $service->generate($periodId);

        $this->assertEquals(
            2,
            $snapshot->version(),
            'Retry logic failed to increment version after conflict.'
        );
    }
}

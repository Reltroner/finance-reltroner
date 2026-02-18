<?php
// tests/Regression/Step54FreezeGuardTest.php

namespace Tests\Regression;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\FiscalPeriod;
use App\Services\Accounting\Snapshot\SnapshotGenerationService;

class Step54FreezeGuardTest extends TestCase
{
    use RefreshDatabase;

    private function createFiscalPeriod(): int
    {
        return FiscalPeriod::factory()->create()->id;
    }

    public function test_snapshot_generation_only_inserts_and_never_deletes(): void
    {
        $periodId = $this->createFiscalPeriod();

        $service = app(SnapshotGenerationService::class);

        $beforeCount = DB::table('financial_snapshots')->count();

        $service->generate($periodId);

        $afterCount = DB::table('financial_snapshots')->count();

        $this->assertGreaterThan(
            $beforeCount,
            $afterCount,
            'Snapshot generation must INSERT exactly one new record.'
        );

        $this->assertEquals(
            $beforeCount + 1,
            $afterCount,
            'Snapshot generation inserted unexpected number of records.'
        );
    }

    public function test_snapshot_generation_does_not_modify_existing_records(): void
    {
        $periodId = $this->createFiscalPeriod();

        $service = app(SnapshotGenerationService::class);

        // First snapshot
        $service->generate($periodId);

        $firstSnapshot = DB::table('financial_snapshots')
            ->where('fiscal_period_id', $periodId)
            ->where('version', 1)
            ->first();

        $originalPayload = $firstSnapshot->payload_json;

        // Second snapshot (new version)
        $service->generate($periodId);

        $secondCount = DB::table('financial_snapshots')
            ->where('fiscal_period_id', $periodId)
            ->count();

        $this->assertEquals(2, $secondCount, 'Versioning failed.');

        $reloadedFirstSnapshot = DB::table('financial_snapshots')
            ->where('id', $firstSnapshot->id)
            ->first();

        $this->assertEquals(
            $originalPayload,
            $reloadedFirstSnapshot->payload_json,
            'Existing snapshot was mutated. Immutability violated.'
        );
    }
}

<?php
// tests/Unit/SnapshotImmutabilityTest.php
namespace Tests\Unit;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Support\Str;
use App\Models\FiscalPeriod;
use Illuminate\Database\QueryException;

class SnapshotImmutabilityTest extends TestCase
{
    public function test_snapshot_cannot_be_updated(): void
    {
        $period = FiscalPeriod::factory()->create();

        $id = (string) Str::uuid();   // ← INI HARUS ADA

        DB::table('financial_snapshots')->insert([
            'id'               => $id,
            'fiscal_period_id' => $period->id,
            'version'          => 1,
            'payload_json'     => '{}',
            'payload_hash'     => hash('sha256', '{}'),
            'source_hash'      => hash('sha256', 'test'),
            'created_at'       => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::table('financial_snapshots')
            ->where('id', $id)
            ->update([
                'payload_json' => '{"tampered": true}'
            ]);
    }
}

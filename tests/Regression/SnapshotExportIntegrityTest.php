<?php
// tests/Regression/SnapshotExportIntegrityTest.php
namespace Tests\Regression;

use Tests\TestCase;
use App\Services\Accounting\Snapshot\SnapshotQueryService;

class SnapshotExportIntegrityTest extends TestCase
{
    public function test_exported_snapshot_payload_matches_hash(): void
    {
        $query = app(SnapshotQueryService::class);

        $snapshot = $query->latest();

        if (!$snapshot) {
            $this->markTestSkipped('No snapshot available.');
        }

        $computedHash = hash(
            'sha256',
            json_encode($snapshot->payload(), JSON_UNESCAPED_UNICODE)
        );

        $this->assertEquals(
            $snapshot->payloadHash(),
            $computedHash,
            'Exported payload does not match stored hash.'
        );
    }
}

<?php
// tests/Unit/Accounting/Snapshot/SnapshotImmutabilityTest.php
namespace Tests\Unit\Accounting\Snapshot;

use ReflectionClass;
use Tests\TestCase;
use App\Services\Accounting\Snapshot\SnapshotGenerationService;
use App\Services\Accounting\Snapshot\SnapshotQueryService;

class SnapshotImmutabilityTest extends TestCase
{
    public function test_snapshot_generation_service_has_no_update_or_delete_method(): void
    {
        $reflection = new ReflectionClass(SnapshotGenerationService::class);

        $methods = array_map(
            fn($m) => $m->getName(),
            $reflection->getMethods()
        );

        $this->assertNotContains('update', $methods);
        $this->assertNotContains('delete', $methods);
        $this->assertNotContains('save', $methods);
    }

    public function test_snapshot_query_service_has_no_mutation_method(): void
    {
        $reflection = new ReflectionClass(SnapshotQueryService::class);

        $methods = array_map(
            fn($m) => $m->getName(),
            $reflection->getMethods()
        );

        $this->assertNotContains('update', $methods);
        $this->assertNotContains('delete', $methods);
        $this->assertNotContains('save', $methods);
    }
}

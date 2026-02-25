<?php
// tests/Unit/Accounting/Budget/BudgetRepositoryTest.php
declare(strict_types=1);

namespace Tests\Unit\Accounting\Budget;

use App\Services\Accounting\Budget\BudgetDefinition;
use App\Services\Accounting\Budget\BudgetRepository;
use App\Services\Accounting\Snapshot\Contracts\SnapshotExistenceChecker;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class BudgetRepositoryTest extends TestCase
{
    public function test_store_batch_requires_snapshot_exists(): void
    {
        $mock = Mockery::mock(SnapshotExistenceChecker::class);
        $mock->shouldReceive('exists')->once()->andReturn(false);

        $repo = new BudgetRepository($mock);

        $this->expectException(\DomainException::class);

        $repo->storeBatch(new Collection([
            new BudgetDefinition('v1', 1, 'revenue', '2026-01', 1000)
        ]));
    }
}
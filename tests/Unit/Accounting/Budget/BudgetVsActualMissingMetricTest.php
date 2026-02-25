<?php
// tests/Unit/Accounting/Budget/BudgetVsActualMissingMetricTest.php
declare(strict_types=1);

namespace Tests\Unit\Accounting\Budget;

use App\Services\Accounting\Budget\BudgetVsActualService;
use App\Services\Accounting\Budget\Contracts\BudgetRepositoryInterface;
use App\Services\Accounting\Budget\Contracts\SnapshotPayloadReader;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use DomainException;
use Mockery;

class BudgetVsActualMissingMetricTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_missing_metric_throws(): void
    {
        $mockSnapshot = Mockery::mock(SnapshotPayloadReader::class);
        $mockRepo = Mockery::mock(BudgetRepositoryInterface::class);

        $snapshotPayload = [
            'revenue' => [
                '2026-01' => 1000,
            ],
        ];

        $mockSnapshot
            ->shouldReceive('getPayload')
            ->with(1, 1)
            ->once()
            ->andReturn($snapshotPayload);

        // Empty collection → metric missing from budget side
        $mockRepo
            ->shouldReceive('getBySnapshotAndVersion')
            ->with(1, 1)
            ->once()
            ->andReturn(collect());

        $service = new BudgetVsActualService($mockSnapshot, $mockRepo);

        $this->expectException(DomainException::class);

        $service->compare(1, 1);
    }
}
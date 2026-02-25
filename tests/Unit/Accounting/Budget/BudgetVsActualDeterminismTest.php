<?php
// tests/Unit/Accounting/Budget/BudgetVsActualDeterminismTest.php
declare(strict_types=1);

namespace Tests\Unit\Accounting\Budget;

use App\Services\Accounting\Budget\BudgetVsActualService;
use App\Services\Accounting\Budget\Contracts\BudgetRepositoryInterface;
use App\Services\Accounting\Budget\Contracts\SnapshotPayloadReader;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Mockery;

class BudgetVsActualDeterminismTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_compare_is_deterministic(): void
    {
        $mockSnapshot = Mockery::mock(SnapshotPayloadReader::class);
        $mockRepo = Mockery::mock(BudgetRepositoryInterface::class);

        $snapshotPayload = [
            'revenue' => [
                '2026-01' => 1000,
            ],
        ];

        $budgetCollection = collect([
            (object) [
                'metric' => 'revenue',
                'period' => '2026-01',
                'amount' => 1000,
            ],
        ]);

        $mockSnapshot
            ->shouldReceive('getPayload')
            ->with(1, 1)
            ->twice()
            ->andReturn($snapshotPayload);

        $mockRepo
            ->shouldReceive('getBySnapshotAndVersion')
            ->with(1, 1)
            ->twice()
            ->andReturn($budgetCollection);

        $service = new BudgetVsActualService($mockSnapshot, $mockRepo);

        $a = $service->compare(1, 1);
        $b = $service->compare(1, 1);

        $this->assertEquals($a, $b);
    }
}
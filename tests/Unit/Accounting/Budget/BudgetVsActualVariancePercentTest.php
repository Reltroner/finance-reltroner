<?php
// tests/Unit/Accounting/Budget/BudgetVsActualVariancePercentTest.php
declare(strict_types=1);

namespace Tests\Unit\Accounting\Budget;

use App\Services\Accounting\Budget\BudgetVsActualService;
use App\Services\Accounting\Budget\Contracts\BudgetRepositoryInterface;
use App\Services\Accounting\Budget\Contracts\SnapshotPayloadReader;
use PHPUnit\Framework\TestCase;
use Mockery;

class BudgetVsActualVariancePercentTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_variance_percent_is_calculated_and_rounded(): void
    {
        $mockSnapshot = Mockery::mock(SnapshotPayloadReader::class);
        $mockRepo = Mockery::mock(BudgetRepositoryInterface::class);

        $mockSnapshot
            ->shouldReceive('getPayload')
            ->with(1, 1)
            ->once()
            ->andReturn([
                'revenue' => ['2026-01' => 110]
            ]);

        $mockRepo
            ->shouldReceive('getBySnapshotAndVersion')
            ->with(1, 1)
            ->once()
            ->andReturn(collect([
                (object)[
                    'metric' => 'revenue',
                    'period' => '2026-01',
                    'amount' => 100
                ]
            ]));

        $service = new BudgetVsActualService($mockSnapshot, $mockRepo);

        $result = $service->compare(1, 1);

        $this->assertEquals(0.1, $result[0]->variance_percent);
    }
}
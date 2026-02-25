<?php
// tests/Unit/Accounting/Budget/BudgetVsActualOrderingTest.php
declare(strict_types=1);

namespace Tests\Unit\Accounting\Budget;

use App\Services\Accounting\Budget\BudgetVsActualService;
use App\Services\Accounting\Budget\Contracts\BudgetRepositoryInterface;
use App\Services\Accounting\Budget\Contracts\SnapshotPayloadReader;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Mockery;

class BudgetVsActualOrderingTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_result_is_explicitly_sorted(): void
    {
        $mockSnapshot = Mockery::mock(SnapshotPayloadReader::class);
        $mockRepo = Mockery::mock(BudgetRepositoryInterface::class);

        $snapshotPayload = [
            'revenue' => [
                '2026-02' => 2000,
                '2026-01' => 1000,
            ],
        ];

        $budgetCollection = collect([
            (object)[
                'metric' => 'revenue',
                'period' => '2026-02',
                'amount' => 2000,
            ],
            (object)[
                'metric' => 'revenue',
                'period' => '2026-01',
                'amount' => 1000,
            ],
        ]);

        $mockSnapshot
            ->shouldReceive('getPayload')
            ->with(1, 1)
            ->once()
            ->andReturn($snapshotPayload);

        $mockRepo
            ->shouldReceive('getBySnapshotAndVersion')
            ->with(1, 1)
            ->once()
            ->andReturn($budgetCollection);

        $service = new BudgetVsActualService($mockSnapshot, $mockRepo);

        $result = $service->compare(1, 1);

        $sorted = $result;

        usort($sorted, function ($a, $b) {
            return [$a->period, $a->metric] <=> [$b->period, $b->metric];
        });

        $this->assertEquals($sorted, $result);
    }
}
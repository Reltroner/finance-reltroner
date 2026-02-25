<?php
// tests/Unit/Accounting/Budget/BudgetVsActualMissingPeriodTest.php
declare(strict_types=1);

namespace Tests\Unit\Accounting\Budget;

use App\Services\Accounting\Budget\BudgetVsActualService;
use App\Services\Accounting\Budget\Contracts\BudgetRepositoryInterface;
use App\Services\Accounting\Budget\Contracts\SnapshotPayloadReader;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Mockery;
use DomainException;

class BudgetVsActualMissingPeriodTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_missing_period_throws(): void
    {
        $mockSnapshot = Mockery::mock(SnapshotPayloadReader::class);
        $mockRepo = Mockery::mock(BudgetRepositoryInterface::class);

        $snapshotPayload = [
            'revenue' => [
                '2026-01' => 1000,
            ],
        ];

        $budgetCollection = collect([
            (object)[
                'metric' => 'revenue',
                'period' => '2026-02', // period berbeda → missing period
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

        $this->expectException(DomainException::class);

        $service->compare(1, 1);
    }
}
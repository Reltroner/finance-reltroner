<?php
// tests/Unit/Accounting/Forecasting/BaselineForecastValidationTest.php
namespace Tests\Unit\Accounting\Forecasting;

use Tests\TestCase;
use App\Services\Accounting\Forecasting\BaselineForecastEngine;
use App\Services\Accounting\Analytics\Forecast\ForecastService;
use App\Services\Accounting\Snapshot\SnapshotDTO;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;

class BaselineForecastValidationTest extends TestCase
{
    private function makeSnapshot(
        int $period,
        float|string $revenue
    ): SnapshotDTO {

        return new SnapshotDTO(
            id: "snap-{$period}",
            fiscalPeriodId: $period,
            version: 1,
            payload: [
                'profit_loss' => [
                    'title' => 'Profit & Loss Statement',
                    'sections' => [
                        [
                            'label' => 'Revenue',
                            'lines' => [],
                            'total' => (string) $revenue,
                        ],
                        [
                            'label' => 'Expenses',
                            'lines' => [],
                            'total' => '0.00',
                        ],
                    ],
                    'grand_total' => (string) $revenue,
                ],
            ],
            payloadHash: 'hash',
            sourceHash: "source-{$period}",
            createdAt: new DateTimeImmutable()
        );
    }

    public function test_insufficient_snapshot_rejection(): void
    {
        $engine = new BaselineForecastEngine(
            new ForecastService()
        );

        $this->expectException(DomainException::class);

        $engine->forecastFromSnapshots(
            [$this->makeSnapshot(202401, 100)],
            'profit_loss.revenue.total',
            2,
            ForecastService::STRATEGY_LINEAR
        );
    }

    public function test_strict_ordering_enforcement(): void
    {
        $engine = new BaselineForecastEngine(
            new ForecastService()
        );

        $snap1 = $this->makeSnapshot(202402, 100);
        $snap2 = $this->makeSnapshot(202401, 120);

        $this->expectException(DomainException::class);

        $engine->forecastFromSnapshots(
            [$snap1, $snap2],
            'profit_loss.revenue.total',
            2,
            ForecastService::STRATEGY_LINEAR
        );
    }

    public function test_unsupported_metric_rejection(): void
    {
        $engine = new BaselineForecastEngine(
            new ForecastService()
        );

        $snap1 = $this->makeSnapshot(202401, 100);
        $snap2 = $this->makeSnapshot(202402, 120);

        $this->expectException(DomainException::class);

        $engine->forecastFromSnapshots(
            [$snap1, $snap2],
            'invalid.metric.path',
            2,
            ForecastService::STRATEGY_LINEAR
        );
    }

    public function test_numeric_cast_safety(): void
    {
        $engine = new BaselineForecastEngine(
            new ForecastService()
        );

        $snap1 = $this->makeSnapshot(202401, 'abc');
        $snap2 = $this->makeSnapshot(202402, 120);

        $this->expectException(DomainException::class);

        $engine->forecastFromSnapshots(
            [$snap1, $snap2],
            'profit_loss.revenue.total',
            2,
            ForecastService::STRATEGY_LINEAR
        );
    }
}
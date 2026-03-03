<?php
// tests/Unit/Accounting/Forecasting/BaselineForecastDeterminismTest.php
namespace Tests\Unit\Accounting\Forecasting;

use Tests\TestCase;
use App\Services\Accounting\Forecasting\BaselineForecastEngine;
use App\Services\Accounting\Analytics\Forecast\ForecastService;
use App\Services\Accounting\Snapshot\SnapshotDTO;
use DateTimeImmutable;

class BaselineForecastDeterminismTest extends TestCase
{
    private function makeSnapshot(
        int $period,
        float $revenue
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
                            'total' => number_format($revenue, 2, '.', ''),
                        ],
                        [
                            'label' => 'Expenses',
                            'lines' => [],
                            'total' => '0.00',
                        ],
                    ],
                    'grand_total' => number_format($revenue, 2, '.', ''),
                ],
            ],
            payloadHash: 'hash',
            sourceHash: "source-{$period}",
            createdAt: new DateTimeImmutable()
        );
    }

    public function test_deterministic_identical_output(): void
    {
        $engine = new BaselineForecastEngine(
            new ForecastService()
        );

        $snapshots = [
            $this->makeSnapshot(202401, 100),
            $this->makeSnapshot(202402, 120),
        ];

        $result1 = $engine->forecastFromSnapshots(
            $snapshots,
            'profit_loss.revenue.total',
            2,
            ForecastService::STRATEGY_LINEAR
        );

        $result2 = $engine->forecastFromSnapshots(
            $snapshots,
            'profit_loss.revenue.total',
            2,
            ForecastService::STRATEGY_LINEAR
        );

        $this->assertEquals(
            $result1->forecastValues(),
            $result2->forecastValues()
        );
    }

    public function test_rounding_enforcement(): void
    {
        $engine = new BaselineForecastEngine(
            new ForecastService()
        );

        $snapshots = [
            $this->makeSnapshot(202401, 100.123456),
            $this->makeSnapshot(202402, 120.987654),
        ];

        $result = $engine->forecastFromSnapshots(
            $snapshots,
            'profit_loss.revenue.total',
            2,
            ForecastService::STRATEGY_LINEAR
        );

        foreach ($result->forecastValues() as $value) {
            $this->assertEquals(
                round($value, 4),
                $value
            );
        }
    }
}
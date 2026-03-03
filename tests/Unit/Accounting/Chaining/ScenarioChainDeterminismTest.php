<?php
// tests/Unit/Accounting/Chaining/ScenarioChainDeterminismTest.php

namespace Tests\Unit\Accounting\Chaining;

use Tests\TestCase;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use App\Support\Clock\FixedClock;
use App\Services\Accounting\Snapshot\SnapshotDTO;
use App\Services\Accounting\Chaining\ScenarioChainService;
use App\Services\Accounting\Chaining\ScenarioChainConfig;
use App\Services\Accounting\Forecasting\BaselineForecastEngine;
use App\Services\Accounting\Hybrid\ForecastVsBudgetService;
use App\Services\Accounting\Risk\RiskEnvelopeService;
use App\Services\Accounting\Risk\RiskModel;
use App\Services\Accounting\Analytics\Forecast\ForecastService;
use App\Services\Accounting\Budget\Contracts\BudgetRepositoryInterface;

class ScenarioChainDeterminismTest extends TestCase
{
    private function makeClock(): FixedClock
    {
        return new FixedClock(
            new DateTimeImmutable('2024-01-01 00:00:00')
        );
    }

    private function makeSnapshot(int $period, float $revenue): SnapshotDTO
    {
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
            createdAt: new DateTimeImmutable('2024-01-01')
        );
    }

    private function makeService(): ScenarioChainService
    {
        $clock = $this->makeClock();

        $forecastService = new ForecastService($clock);

        $forecastEngine = new BaselineForecastEngine(
            $forecastService
        );

        $budgetRepo = new class implements BudgetRepositoryInterface {

            public function getBySnapshotAndVersion(int $fiscalPeriodId, int $version): Collection
            {
                return collect();
            }

            public function getByMetricAndVersion(string $metric, int $version): Collection
            {
                return collect([
                    ['period' => 202403, 'value' => 130.00],
                    ['period' => 202404, 'value' => 140.00],
                ]);
            }
        };

        $hybrid = new ForecastVsBudgetService(
            $forecastEngine,
            $budgetRepo
        );

        $risk = new RiskEnvelopeService($clock);

        return new ScenarioChainService(
            $forecastEngine,
            $hybrid,
            $risk,
            $clock
        );
    }

    public function test_same_input_produces_identical_output(): void
    {
        $service = $this->makeService();

        $snapshots = [
            $this->makeSnapshot(202401, 100),
            $this->makeSnapshot(202402, 120),
        ];

        $config = new ScenarioChainConfig(
            preRiskModel: new RiskModel(0.1, 1, 1),
            postHybridRiskModel: new RiskModel(0.05, 1, 1),
            applyPreRisk: true,
            applyPostHybridRisk: true
        );

        $result1 = $service->execute(
            $snapshots,
            'profit_loss.revenue.total',
            2,
            ForecastService::STRATEGY_LINEAR,
            null,
            1,
            $config
        );

        $result2 = $service->execute(
            $snapshots,
            'profit_loss.revenue.total',
            2,
            ForecastService::STRATEGY_LINEAR,
            null,
            1,
            $config
        );

        $this->assertEquals($result1, $result2);
    }
}
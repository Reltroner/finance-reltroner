<?php
// tests/Unit/Accounting/Hybrid/ForecastVsBudgetTest.php

namespace Tests\Unit\Accounting\Hybrid;

use Tests\TestCase;
use DateTimeImmutable;
use Illuminate\Support\Collection;
use DomainException;
use App\Services\Accounting\Hybrid\ForecastVsBudgetService;
use App\Services\Accounting\Forecasting\BaselineForecastEngine;
use App\Services\Accounting\Analytics\Forecast\ForecastService;
use App\Services\Accounting\Snapshot\SnapshotDTO;
use App\Services\Accounting\Budget\Contracts\BudgetRepositoryInterface;

class ForecastVsBudgetTest extends TestCase
{
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
            createdAt: new DateTimeImmutable('2024-01-01') // deterministic
        );
    }

    private function mockBudget(array $data): BudgetRepositoryInterface
    {
        return new class($data) implements BudgetRepositoryInterface {

            public function __construct(private array $data) {}

            public function getByMetricAndVersion(
                string $metric,
                int $version
            ): Collection {

                $rows = [];

                foreach ($this->data as $period => $value) {
                    $rows[] = [
                        'period' => $period,
                        'value'  => $value,
                    ];
                }

                return collect($rows);
            }

            public function getBySnapshotAndVersion(
                int $fiscalPeriodId,
                int $version
            ): Collection {
                return collect();
            }
        };
    }

    public function test_deterministic_identical_output(): void
    {
        $forecastEngine = new BaselineForecastEngine(
            new ForecastService()
        );

        $budgetRepo = $this->mockBudget([
            202403 => 150,
            202404 => 160,
        ]);

        $service = new ForecastVsBudgetService(
            $forecastEngine,
            $budgetRepo
        );

        $snapshots = [
            $this->makeSnapshot(202401, 100),
            $this->makeSnapshot(202402, 120),
        ];

        $result1 = $service->compareForecastToBudget(
            $snapshots,
            'profit_loss.revenue.total',
            2,
            ForecastService::STRATEGY_LINEAR,
            1
        );

        $result2 = $service->compareForecastToBudget(
            $snapshots,
            'profit_loss.revenue.total',
            2,
            ForecastService::STRATEGY_LINEAR,
            1
        );

        $this->assertEquals($result1, $result2);
    }

    public function test_missing_budget_period_rejection(): void
    {
        $forecastEngine = new BaselineForecastEngine(
            new ForecastService()
        );

        $budgetRepo = $this->mockBudget([
            202403 => 150,
            // 202404 missing
        ]);

        $service = new ForecastVsBudgetService(
            $forecastEngine,
            $budgetRepo
        );

        $snapshots = [
            $this->makeSnapshot(202401, 100),
            $this->makeSnapshot(202402, 120),
        ];

        $this->expectException(DomainException::class);

        $service->compareForecastToBudget(
            $snapshots,
            'profit_loss.revenue.total',
            2,
            ForecastService::STRATEGY_LINEAR,
            1
        );
    }

    public function test_zero_budget_division_guard(): void
    {
        $forecastEngine = new BaselineForecastEngine(
            new ForecastService()
        );

        $budgetRepo = $this->mockBudget([
            202403 => 0.0,
            202404 => 0.0,
        ]);

        $service = new ForecastVsBudgetService(
            $forecastEngine,
            $budgetRepo
        );

        $snapshots = [
            $this->makeSnapshot(202401, 100),
            $this->makeSnapshot(202402, 120),
        ];

        $result = $service->compareForecastToBudget(
            $snapshots,
            'profit_loss.revenue.total',
            2,
            ForecastService::STRATEGY_LINEAR,
            1
        );

        foreach ($result->variancePercent as $percent) {
            $this->assertEquals(0.0, $percent);
        }
    }

    public function test_rounding_enforcement(): void
    {
        $forecastEngine = new BaselineForecastEngine(
            new ForecastService()
        );

        $budgetRepo = $this->mockBudget([
            202403 => 150.123456,
            202404 => 160.654321,
        ]);

        $service = new ForecastVsBudgetService(
            $forecastEngine,
            $budgetRepo
        );

        $snapshots = [
            $this->makeSnapshot(202401, 100.123456),
            $this->makeSnapshot(202402, 120.987654),
        ];

        $result = $service->compareForecastToBudget(
            $snapshots,
            'profit_loss.revenue.total',
            2,
            ForecastService::STRATEGY_LINEAR,
            1
        );

        foreach ($result->variance as $v) {
            $this->assertEquals(round($v, 4), $v);
        }

        foreach ($result->variancePercent as $p) {
            $this->assertEquals(round($p, 4), $p);
        }
    }
}
<?php
// tests/Unit/Accounting/Analytics/KPIRevenueGrowthTest.php
namespace Tests\Unit\Accounting\Analytics;

use Tests\TestCase;
use App\Services\Accounting\Analytics\FinancialKPIService;
use App\Services\Accounting\Snapshot\SnapshotAggregateDTO;
use DateTimeImmutable;
use DomainException;

class KPIRevenueGrowthTest extends TestCase
{
    private function makeSnapshot(float $revenue): SnapshotAggregateDTO
    {
        return new SnapshotAggregateDTO(
            snapshotId: uniqid(),
            fiscalPeriodId: 1,
            version: 1,
            statements: [
                'profit_loss' => [
                    'revenue' => $revenue,
                    'cost_of_goods_sold' => 100,
                    'operating_income' => 100,
                    'net_income' => 100,
                ],
                'balance_sheet' => [
                    'current_assets' => 100,
                    'current_liabilities' => 50,
                    'total_liabilities' => 200,
                    'total_equity' => 300,
                ],
            ],
            payloadHash: 'payload',
            sourceHash: 'source',
            createdAt: new DateTimeImmutable()
        );
    }

    public function test_revenue_growth_is_calculated_correctly(): void
    {
        $service = new FinancialKPIService();

        $previous = $this->makeSnapshot(1000);
        $current = $this->makeSnapshot(1200);

        $kpi = $service->compute($current, $previous);

        $metrics = $kpi->kpis();

        $expected = round(((1200 - 1000) / 1000) * 100, 4);

        $this->assertEquals(
            $expected,
            $metrics['revenue_growth_percent']
        );
    }

    public function test_missing_previous_snapshot_throws_exception(): void
    {
        $service = new FinancialKPIService();

        $current = $this->makeSnapshot(1200);

        $this->expectException(\DomainException::class);

        $service->compute($current);
    }

    public function test_previous_revenue_zero_throws_exception(): void
    {
        $service = new FinancialKPIService();

        $previous = $this->makeSnapshot(0);
        $current = $this->makeSnapshot(1000);

        $this->expectException(DomainException::class);

        $service->compute($current, $previous);
    }
}

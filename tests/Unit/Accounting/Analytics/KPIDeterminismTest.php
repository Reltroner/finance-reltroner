<?php
// tests/Unit/Accounting/Analytics/KPIDeterminismTest.php
namespace Tests\Unit\Accounting\Analytics;

use Tests\TestCase;
use App\Services\Accounting\Analytics\FinancialKPIService;
use App\Services\Accounting\Snapshot\SnapshotAggregateDTO;
use DateTimeImmutable;

class KPIDeterminismTest extends TestCase
{
    private function makeSnapshot(): SnapshotAggregateDTO
    {
        return new SnapshotAggregateDTO(
            snapshotId: 'snapshot-1',
            fiscalPeriodId: 1,
            version: 1,
            statements: [
                'profit_loss' => [
                    'revenue' => 1500,
                    'cost_of_goods_sold' => 500,
                    'operating_income' => 600,
                    'net_income' => 450,
                ],
                'balance_sheet' => [
                    'current_assets' => 900,
                    'current_liabilities' => 300,
                    'total_liabilities' => 700,
                    'total_equity' => 800,
                ],
            ],
            payloadHash: 'hash-a',
            sourceHash: 'hash-b',
            createdAt: new DateTimeImmutable()
        );
    }

    public function test_same_snapshot_produces_identical_kpis(): void
    {
        $service = new FinancialKPIService();

        $snapshot = $this->makeSnapshot();

        $kpi1 = $service->compute($snapshot, $snapshot);
        $kpi2 = $service->compute($snapshot, $snapshot);

        $this->assertEquals(
            $kpi1->kpis(),
            $kpi2->kpis(),
            'KPI computation is not deterministic for identical snapshot.'
        );

        $this->assertEquals(
            $kpi1->payloadHash(),
            $kpi2->payloadHash()
        );

        $this->assertEquals(
            $kpi1->sourceHash(),
            $kpi2->sourceHash()
        );
    }

    public function test_identical_payload_hash_means_identical_kpi_output(): void
    {
        $service = new FinancialKPIService();

        $snapshot1 = $this->makeSnapshot();
        $snapshot2 = $this->makeSnapshot();

        $kpi1 = $service->compute($snapshot1, $snapshot1);
        $kpi2 = $service->compute($snapshot2, $snapshot2);

        $this->assertEquals(
            $kpi1->kpis(),
            $kpi2->kpis(),
            'Same payload hash should yield identical KPI values.'
        );
    }
}

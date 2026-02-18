<?php
// tests/Unit/Accounting/Analytics/FinancialKPIFormulaTest.php
namespace Tests\Unit\Accounting\Analytics;

use Tests\TestCase;
use App\Services\Accounting\Analytics\FinancialKPIService;
use App\Services\Accounting\Snapshot\SnapshotAggregateDTO;
use DateTimeImmutable;
use DomainException;

class FinancialKPIFormulaTest extends TestCase
{
    private function makeSnapshot(array $overrides = []): SnapshotAggregateDTO
    {
        $statements = [
            'profit_loss' => [
                'revenue' => 1000,
                'cost_of_goods_sold' => 400,
                'operating_income' => 300,
                'net_income' => 200,
            ],
            'balance_sheet' => [
                'current_assets' => 800,
                'current_liabilities' => 400,
                'total_liabilities' => 500,
                'total_equity' => 500,
            ],
        ];

        foreach ($overrides as $statement => $values) {
            foreach ($values as $key => $value) {
                $statements[$statement][$key] = $value;
            }
        }

        return new SnapshotAggregateDTO(
            snapshotId: 'test-id',
            fiscalPeriodId: 1,
            version: 1,
            statements: $statements,
            payloadHash: 'payload',
            sourceHash: 'source',
            createdAt: new DateTimeImmutable()
        );
    }

    public function test_kpi_formulas_are_correct(): void
    {
        $service = new FinancialKPIService();

        $current = $this->makeSnapshot();
        $previous = $this->makeSnapshot();

        $kpi = $service->compute($current, $previous);

        $metrics = $kpi->kpis();

        $this->assertEquals(60.0, $metrics['gross_margin_percent']);
        $this->assertEquals(30.0, $metrics['operating_margin_percent']);
        $this->assertEquals(20.0, $metrics['net_margin_percent']);
        $this->assertEquals(2.0, $metrics['current_ratio']);
        $this->assertEquals(1.0, $metrics['debt_to_equity']);
    }

    public function test_division_by_zero_throws_exception(): void
    {
        $service = new FinancialKPIService();

        $current = $this->makeSnapshot([
            'profit_loss' => [
                'revenue' => 0
            ]
        ]);

        $previous = $this->makeSnapshot();

        $this->expectException(DomainException::class);

        $service->compute($current, $previous);
    }

    public function test_rounding_is_deterministic(): void
    {
        $service = new FinancialKPIService();

        $current = $this->makeSnapshot([
            'profit_loss' => [
                'revenue' => 3,
                'cost_of_goods_sold' => 1,
                'operating_income' => 1,
                'net_income' => 1,
            ],
            'balance_sheet' => [
                'current_assets' => 5,
                'current_liabilities' => 3,
                'total_liabilities' => 7,
                'total_equity' => 9,
            ],
        ]);

        $previous = $this->makeSnapshot();

        $kpi = $service->compute($current, $previous);

        $metrics = $kpi->kpis();

        $this->assertEquals(
            round(((3 - 1) / 3) * 100, 4),
            $metrics['gross_margin_percent']
        );

        $this->assertEquals(
            round(1 / 3 * 100, 4),
            $metrics['operating_margin_percent']
        );
    }
}

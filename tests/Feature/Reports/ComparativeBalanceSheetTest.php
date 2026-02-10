<?php
// tests/Feature/Reports/ComparativeProfitLossTest.php
namespace Tests\Feature\Reports;

use Tests\Feature\FeatureTestCase;
use Tests\Support\Seeds\ReadOnlyLedgerSeeder;
use App\Services\Accounting\Read\Statements\Comparative\ComparativeBalanceSheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComparativeBalanceSheetTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_comparative_balance_sheet_preserves_equation_per_period(): void
    {
        $p1 = ReadOnlyLedgerSeeder::seed(2024, 1)['period'];
        $p2 = ReadOnlyLedgerSeeder::seed(2024, 2)['period'];

        $statement = app(ComparativeBalanceSheetService::class)
            ->generate([
                $p1->id => 'Period A',
                $p2->id => 'Period B',
            ]);

        // Two independent balance sheet snapshots
        $this->assertCount(2, $statement->periods);

        foreach ($statement->periods as $periodSnapshot) {
            $bs = $periodSnapshot->statement;

            $this->assertCount(3, $bs->sections);

            $assets      = $bs->sections[0]->total();
            $liabilities = $bs->sections[1]->total();
            $equity      = $bs->sections[2]->total();

            // Accounting equation must hold PER PERIOD
            $this->assertEquals(
                $assets,
                $liabilities + $equity
            );
        }
    }
}

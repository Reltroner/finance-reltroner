<?php
// tests/Feature/Reports/ProfitLossTest.php
namespace Tests\Feature\Reports;

use Tests\Feature\FeatureTestCase;
use Tests\Support\Seeds\ReadOnlyLedgerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BalanceSheetTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_balance_sheet_equation_holds(): void
    {
        $data = ReadOnlyLedgerSeeder::seed();

        $response = $this->get(
            "/reports/balance-sheet/{$data['period']->id}"
        );

        $response->assertStatus(200);
        $response->assertViewHas('statement');

        $statement = $response->viewData('statement');

        $this->assertEquals(
            'Balance Sheet',
            $statement->title
        );

        // Expected sections: Assets, Liabilities, Equity
        $this->assertCount(3, $statement->sections);

        $assets      = $statement->sections[0]->total();
        $liabilities = $statement->sections[1]->total();
        $equity      = $statement->sections[2]->total();

        // Fundamental accounting invariant
        $this->assertEquals(
            $assets,
            $liabilities + $equity
        );
    }
}

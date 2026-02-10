<?php
// tests/Feature/Reports/ProfitLossTest.php
namespace Tests\Feature\Reports;

use Tests\Feature\FeatureTestCase;
use Tests\Support\Seeds\ReadOnlyLedgerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfitLossTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_profit_loss_projection_is_deterministic(): void
    {
        $data = ReadOnlyLedgerSeeder::seed();

        $response = $this->get(
            "/reports/profit-loss/{$data['period']->id}"
        );

        $response->assertStatus(200);
        $response->assertViewHas('statement');

        $statement = $response->viewData('statement');

        $this->assertEquals(
            'Profit & Loss Statement',
            $statement->title
        );

        $this->assertCount(2, $statement->sections);

        $totalRevenue  = $statement->sections[0]->total();
        $totalExpenses = $statement->sections[1]->total();

        // P&L grand total must be derivable from sections
        $this->assertEquals(
            $totalRevenue + $totalExpenses,
            $statement->grandTotal()
        );
    }
}

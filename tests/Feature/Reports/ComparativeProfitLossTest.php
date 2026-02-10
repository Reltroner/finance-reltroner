<?php
// tests/Feature/Reports/ComparativeProfitLossTest.php

namespace Tests\Feature\Reports;

use Tests\Feature\FeatureTestCase;
use Tests\Support\Seeds\ReadOnlyLedgerSeeder;
use App\Services\Accounting\Read\Statements\Comparative\ComparativeProfitLossService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComparativeProfitLossTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_comparative_profit_loss_contains_multiple_periods(): void
    {
        // Seed two independent fiscal periods (balance-sheet safe, P&L optional)
        $seed1 = ReadOnlyLedgerSeeder::seed(2024, 1);
        $seed2 = ReadOnlyLedgerSeeder::seed(2024, 2);

        $p1 = $seed1['period'];
        $p2 = $seed2['period'];

        $statement = app(ComparativeProfitLossService::class)->generate([
            $p1->id => 'Period A',
            $p2->id => 'Period B',
        ]);

        // Two independent P&L snapshots must exist
        $this->assertCount(2, $statement->periods);

        // Comparative lines MAY be empty (no revenue/expense in read-only ledger)
        $this->assertIsArray($statement->lines);

        // If lines exist, each line must contain amount for every period
        foreach ($statement->lines as $line) {
            $this->assertArrayHasKey($p1->id, $line->amounts);
            $this->assertArrayHasKey($p2->id, $line->amounts);
        }
    }
}

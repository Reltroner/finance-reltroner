<?php
// tests/Support/AssertsLedger.php
namespace Tests\Support;

trait AssertsLedger
{
    protected function assertBalanced(array $details): void
    {
        $debit  = collect($details)->sum('debit');
        $credit = collect($details)->sum('credit');

        $this->assertEquals(
            round($debit, 2),
            round($credit, 2),
            'Ledger is not balanced'
        );
    }
}

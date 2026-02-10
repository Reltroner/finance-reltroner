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

    protected function assertValidAccount(Account $account): void
    {
        $this->assertTrue(
            in_array($account->type, [
                Account::TYPE_ASSET,
                Account::TYPE_LIABILITY,
                Account::TYPE_EQUITY,
                Account::TYPE_INCOME,
                Account::TYPE_EXPENSE,
            ])
        );

        $this->assertTrue(
            in_array($account->normal_balance, [
                Account::NORMAL_DEBIT,
                Account::NORMAL_CREDIT,
            ])
        );
    }
}

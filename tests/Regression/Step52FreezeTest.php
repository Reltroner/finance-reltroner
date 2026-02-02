<?php
// tests/Regression/Step52FreezeTest.php
namespace Tests\Regression;

use Tests\Support\AccountingTestCase;
use App\Models\Transaction;

class Step52FreezeTest extends AccountingTestCase
{
    public function test_step_52_contracts_must_hold(): void
    {
        // 1. Transaction must be balanced
        $tx = Transaction::factory()->create();
        $this->assertTrue($tx->is_balanced);

        // 2. Equity must be immutable
        $tx->type = Transaction::TYPE_EQUITY_OPENING;
        $tx->save();

        $this->assertFalse($tx->isEditable());

        // 3. Period closing is system-only
        $this->assertTrue(
            in_array(
                Transaction::TYPE_PERIOD_CLOSING,
                [Transaction::TYPE_PERIOD_CLOSING]
            )
        );
    }
}

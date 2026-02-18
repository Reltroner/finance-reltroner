<?php
// tests/Unit/Accounting/Snapshot/LedgerStateHasherTest.php
namespace Tests\Unit\Accounting\Snapshot;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\FiscalPeriod;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\Accounting\Snapshot\LedgerStateHasher;

class LedgerStateHasherTest extends TestCase
{
    use RefreshDatabase;

    private function seedLedger(int $periodId, float $amount): void
    {
        $account = Account::factory()->create();

        $transaction = Transaction::factory()->create([
            'fiscal_period_id' => $periodId,
            'total_debit'      => $amount,
            'total_credit'     => $amount,
        ]);

        TransactionDetail::factory()->create([
            'transaction_id' => $transaction->id,
            'account_id'     => $account->id,
            'debit'          => $amount,
            'credit'         => 0,
        ]);

        TransactionDetail::factory()->create([
            'transaction_id' => $transaction->id,
            'account_id'     => $account->id,
            'debit'          => 0,
            'credit'         => $amount,
        ]);
    }

    public function test_same_ledger_state_produces_same_source_hash(): void
    {
        $period = FiscalPeriod::factory()->create([
            'year'   => 2025,
            'period' => 1,
        ]);

        $this->seedLedger($period->id, 1000);

        $hasher = new LedgerStateHasher();

        $hash1 = $hasher->hashForFiscalPeriod(
            $period->year,
            $period->period
        );

        $hash2 = $hasher->hashForFiscalPeriod(
            $period->year,
            $period->period
        );

        $this->assertEquals(
            $hash1,
            $hash2,
            'LedgerStateHasher is not deterministic for identical ledger state.'
        );
    }

    public function test_ledger_modification_produces_different_source_hash(): void
    {
        $period = FiscalPeriod::factory()->create([
            'year'   => 2025,
            'period' => 1,
        ]);

        $this->seedLedger($period->id, 1000);

        $hasher = new LedgerStateHasher();

        $originalHash = $hasher->hashForFiscalPeriod(
            $period->year,
            $period->period
        );

        // Modify ledger
        $this->seedLedger($period->id, 500);

        $newHash = $hasher->hashForFiscalPeriod(
            $period->year,
            $period->period
        );

        $this->assertNotEquals(
            $originalHash,
            $newHash,
            'LedgerStateHasher failed to detect ledger modification.'
        );
    }
}

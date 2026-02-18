<?php
// tests/Unit/Accounting/Snapshot/SnapshotHashDeterminismTest.php
namespace Tests\Unit\Accounting\Snapshot;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\FiscalPeriod;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\Accounting\Snapshot\SnapshotGenerationService;

class SnapshotHashDeterminismTest extends TestCase
{
    use RefreshDatabase;

    private function seedMinimalLedger(int $periodId, Account $account): void
    {
        $transaction = Transaction::factory()
            ->forPeriod(2025, 1) // FIXED DATE
            ->state([
                'fiscal_period_id' => $periodId,
                'exchange_rate'    => 1,
                'total_debit'      => 1000,
                'total_credit'     => 1000,
                'total_debit_base' => 1000,
                'total_credit_base'=> 1000,
            ])
            ->create();

        TransactionDetail::factory()->create([
            'transaction_id' => $transaction->id,
            'account_id'     => $account->id,
            'line_no'        => 1,
            'debit'          => 1000,
            'credit'         => 0,
        ]);

        TransactionDetail::factory()->create([
            'transaction_id' => $transaction->id,
            'account_id'     => $account->id,
            'line_no'        => 2,
            'debit'          => 0,
            'credit'         => 1000,
        ]);
    }

    public function test_same_ledger_state_produces_identical_payload_hash(): void
    {
        $period1 = FiscalPeriod::factory()->create([
            'year'   => 2025,
            'period' => 1,
        ]);

        $period2 = FiscalPeriod::factory()->create([
            'year'   => 2025,
            'period' => 2,
        ]);

        // 🔒 Use ONE deterministic account for both ledgers
        $account = Account::factory()->create([
            'code' => '1001',
            'name' => 'Cash Test',
        ]);

        $this->seedDeterministicLedger($period1->id, $period1->year, $period1->period, $account);
        $this->seedDeterministicLedger($period2->id, $period2->year, $period2->period, $account);

        $service = app(SnapshotGenerationService::class);

        $snapshot1 = $service->generate($period1->id);
        $snapshot2 = $service->generate($period2->id);

        $this->assertEquals(
            $snapshot1->payloadHash(),
            $snapshot2->payloadHash(),
            'Determinism violated: identical ledger state produced different payload_hash.'
        );
    }

    private function seedDeterministicLedger(
        int $periodId,
        int $year,
        int $period,
        Account $account
    ): void {

        $transaction = Transaction::factory()
            ->forPeriod($year, $period)
            ->state([
                'fiscal_period_id'  => $periodId,
                'exchange_rate'     => 1,
                'total_debit'       => 1000,
                'total_credit'      => 1000,
                'total_debit_base'  => 1000,
                'total_credit_base' => 1000,
                'status'            => 'posted',
                'posted_at' => '2025-01-01 00:00:00',
            ])
            ->create();

        TransactionDetail::factory()->create([
            'transaction_id' => $transaction->id,
            'account_id'     => $account->id,
            'line_no'        => 1,
            'debit'          => 1000,
            'credit'         => 0,
        ]);

        TransactionDetail::factory()->create([
            'transaction_id' => $transaction->id,
            'account_id'     => $account->id,
            'line_no'        => 2,
            'debit'          => 0,
            'credit'         => 1000,
        ]);
    }
}

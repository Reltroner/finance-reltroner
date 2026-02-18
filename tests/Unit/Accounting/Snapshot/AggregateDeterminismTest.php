<?php
// tests/Unit/Accounting/Snapshot/AggregateDeterminismTest.php
namespace Tests\Unit\Accounting\Snapshot;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\FiscalPeriod;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\Accounting\Snapshot\MultiStatementSnapshotService;

class AggregateDeterminismTest extends TestCase
{
    use RefreshDatabase;

    private function seedLedger(int $periodId, float $amount): void
    {
        $account = Account::factory()->create();

        $transaction = Transaction::factory()->create([
            'fiscal_period_id' => $periodId,
            'total_debit' => $amount,
            'total_credit' => $amount,
        ]);

        TransactionDetail::factory()->create([
            'transaction_id' => $transaction->id,
            'account_id' => $account->id,
            'debit' => $amount,
            'credit' => 0,
        ]);

        TransactionDetail::factory()->create([
            'transaction_id' => $transaction->id,
            'account_id' => $account->id,
            'debit' => 0,
            'credit' => $amount,
        ]);
    }

    public function test_same_ledger_state_produces_identical_aggregate(): void
    {
        $period1 = FiscalPeriod::factory()->create([
            'year'   => 2025,
            'period' => 1,
        ]);

        $period2 = FiscalPeriod::factory()->create([
            'year'   => 2025,
            'period' => 2,
        ]);

        // 🔒 deterministic account
        $account = Account::factory()->create([
            'code'           => '1001',
            'name'           => 'Cash Test',
            'type'           => Account::TYPE_ASSET,
            'normal_balance' => Account::NORMAL_DEBIT,
        ]);

        $this->seedDeterministicLedger($period1, $account, 1000);
        $this->seedDeterministicLedger($period2, $account, 1000);

        $service = app(MultiStatementSnapshotService::class);

        $aggregate1 = $service->generate($period1->id);
        $aggregate2 = $service->generate($period2->id);

        $this->assertEquals($aggregate1->payloadHash(), $aggregate2->payloadHash());
        $this->assertEquals($aggregate1->sourceHash(), $aggregate2->sourceHash());
        $this->assertEquals($aggregate1->statements(), $aggregate2->statements());
    }

    private function seedDeterministicLedger(
        FiscalPeriod $period,
        Account $account,
        float $amount
    ): void {

        $transaction = Transaction::factory()
            ->forPeriod($period->year, $period->period)
            ->state([
                'fiscal_period_id'  => $period->id,
                'exchange_rate'     => 1,
                'total_debit'       => $amount,
                'total_credit'      => $amount,
                'total_debit_base'  => $amount,
                'total_credit_base' => $amount,
                'status'            => 'posted',
                'posted_at'         => now(),
            ])
            ->create();

        TransactionDetail::factory()->create([
            'transaction_id' => $transaction->id,
            'account_id'     => $account->id,
            'line_no'        => 1,
            'debit'          => $amount,
            'credit'         => 0,
        ]);

        TransactionDetail::factory()->create([
            'transaction_id' => $transaction->id,
            'account_id'     => $account->id,
            'line_no'        => 2,
            'debit'          => 0,
            'credit'         => $amount,
        ]);
    }
}

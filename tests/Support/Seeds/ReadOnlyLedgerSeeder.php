<?php
// tests/Support/Seeds/ReadOnlyLedgerSeeder.php
namespace Tests\Support\Seeds;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Tests\Support\Seeds\ChartOfAccountsSeeder;

class ReadOnlyLedgerSeeder
{
    public static function seed(int $year = 2024, int $periodNo = 1): array
    {
        // 1️⃣ Seed Chart of Accounts terlebih dahulu (idempotent)
        ChartOfAccountsSeeder::seed();

        // ✅ INIT WAJIB
        $lineNo = 1;

        // 2️⃣ READ TEST → period HARUS open
        $period = FiscalPeriod::firstOrCreate(
            ['year' => $year, 'period' => $periodNo],
            ['status' => 'open']
        );

        // 3️⃣ Ambil akun dari COA (bukan create baru)
        $cash    = Account::where('code', '1000')->firstOrFail();
        $revenue = Account::where('code', '4000')->firstOrFail();

        // 4️⃣ Transaction READ-SAFE
        $txn = Transaction::factory()->create([
            'fiscal_year'      => $period->year,
            'fiscal_period'    => $period->period,
            'transaction_type' => Transaction::TYPE_SYSTEM_ADJUSTMENT,
            'type'             => Transaction::TYPE_SYSTEM_ADJUSTMENT,
            'status'           => 'posted',
        ]);

        // 5️⃣ Journal lines (deterministic)
        TransactionDetail::factory()->create([
            'transaction_id' => $txn->id,
            'line_no'        => $lineNo++, // ✅ sekarang aman
            'account_id'     => $cash->id,
            'debit'          => 1000,
            'credit'         => 0,
        ]);

        TransactionDetail::factory()->create([
            'transaction_id' => $txn->id,
            'line_no'        => $lineNo++,
            'account_id'     => $revenue->id,
            'debit'          => 0,
            'credit'         => 1000,
        ]);

        return compact('period', 'cash', 'revenue');
    }
}

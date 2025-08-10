<?php
// database/seeders/TransactionDetailSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Account;
use App\Models\CostCenter;

class TransactionDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            // Pastikan ada minimal beberapa akun untuk komposisi realistis
            if (Account::count() < 6) {
                $defaults = [
                    ['code' => '1001', 'name' => 'Cash'],
                    ['code' => '1101', 'name' => 'Bank'],
                    ['code' => '1201', 'name' => 'Accounts Receivable'],
                    ['code' => '2001', 'name' => 'Accounts Payable'],
                    ['code' => '4001', 'name' => 'Sales Revenue'],
                    ['code' => '5001', 'name' => 'COGS'],
                    ['code' => '5101', 'name' => 'Salary Expense'],
                    ['code' => '5201', 'name' => 'Rent Expense'],
                    ['code' => '5301', 'name' => 'Utilities Expense'],
                    ['code' => '3999', 'name' => 'Suspense'],
                ];
                foreach ($defaults as $a) {
                    Account::firstOrCreate(['code' => $a['code']], ['name' => $a['name']]);
                }
            }

            // Cost center opsional
            if (CostCenter::count() === 0) {
                CostCenter::create(['name' => 'Head Office']);
                CostCenter::create(['name' => 'Sales']);
                CostCenter::create(['name' => 'Production']);
            }

            $accountIds    = Account::pluck('id', 'code'); // map code => id
            $accountPool   = array_values($accountIds->all());
            $costCenterIds = CostCenter::pluck('id')->all();

            // Proses per chunk agar hemat memori
            Transaction::query()->orderBy('id')->chunkById(100, function ($chunk) use ($accountIds, $accountPool, $costCenterIds) {
                foreach ($chunk as $tx) {

                    // Hitung line_no awal
                    $startLineNo = (int) TransactionDetail::where('transaction_id', $tx->id)->max('line_no');
                    $lineNo = $startLineNo > 0 ? $startLineNo : 0;

                    // Jika belum ada detail: generate lengkap 2–6 baris balanced
                    if ($tx->details()->count() === 0) {
                        $lines = rand(2, 6);
                        $this->createBalancedLines($tx, $lines, $lineNo, $accountPool, $costCenterIds);
                    } else {
                        // Sudah ada: cek balanced; jika belum, tambahkan balancing line
                        $sumD = (float) $tx->details()->sum('debit');
                        $sumC = (float) $tx->details()->sum('credit');
                        $diff = round($sumD - $sumC, 2);

                        if ($diff !== 0.0) {
                            $lineNo++;
                            $suspenseId = $accountIds['3999'] ?? Arr::random($accountPool);
                            // Jika debit > credit, tambahkan CREDIT sebesar selisih (kebalikannya juga)
                            $debit  = $diff < 0 ? abs($diff) : 0;
                            $credit = $diff > 0 ? $diff : 0;

                            TransactionDetail::create([
                                'transaction_id' => $tx->id,
                                'line_no'        => $lineNo,
                                'account_id'     => $suspenseId,
                                'debit'          => $debit,
                                'credit'         => $credit,
                                'cost_center_id' => Arr::random(array_merge($costCenterIds, [null, null])),
                                'memo'           => 'Auto balancing line',
                            ]);
                        }
                    }

                    // Recalculate header totals & base totals
                    $this->recalcHeader($tx->id);
                }
            });
        });
    }

    /**
     * Buat n baris one-sided yang balanced untuk transaksi tertentu.
     * - Jika header total (total_debit/credit) = 0, akan di-generate otomatis.
     * - Komposisi: selang-seling debit/credit, baris terakhir jadi balancing.
     */
    protected function createBalancedLines(
        Transaction $tx,
        int $lines,
        int &$lineNo,
        array $accountPool,
        array $costCenterIds
    ): void
    {
        $lines = max(2, $lines);

        $total = max(
            round((float) $tx->total_debit, 2),
            round((float) $tx->total_credit, 2)
        );

        // Jika header total = 0, generate nilai random yang enak
        if ($total <= 0) {
            $total = round(random_int(5000, 250000) / 100, 2); // 50.00 – 2500.00
            $tx->forceFill([
                'total_debit'  => $total,
                'total_credit' => $total,
            ])->save();
        }

        // Distribusi ke baris: n-1 baris random, baris ke-n balancing
        $remainingDebit  = $total;
        $remainingCredit = $total;

        for ($i = 0; $i < $lines; $i++) {
            $lineNo++;
            $isDebit = ($i % 2 === 0);

            // Baris terakhir = balancing
            if ($i === $lines - 1) {
                $amount = $isDebit ? $remainingDebit : $remainingCredit;
            } else {
                $cap    = max(1, $total / $lines);
                $amount = round(random_int(100, (int)($cap * 100)) / 100, 2);
                if ($isDebit) {
                    $amount = min($amount, $remainingDebit);
                } else {
                    $amount = min($amount, $remainingCredit);
                }
            }

            $debit  = $isDebit ? $amount : 0;
            $credit = $isDebit ? 0 : $amount;

            if ($isDebit) {
                $remainingDebit  = round($remainingDebit  - $debit, 2);
            } else {
                $remainingCredit = round($remainingCredit - $credit, 2);
            }

            TransactionDetail::create([
                'transaction_id' => $tx->id,
                'line_no'        => $lineNo,
                'account_id'     => Arr::random($accountPool),
                'debit'          => $debit,
                'credit'         => $credit,
                'cost_center_id' => Arr::random(array_merge($costCenterIds, [null, null])),
                'memo'           => $isDebit ? 'Auto debit' : 'Auto credit',
            ]);
        }
    }

    /**
     * Recalculate header totals (base & trx currency).
     */
    protected function recalcHeader(int $transactionId): void
    {
        /** @var Transaction|null $tx */
        $tx = Transaction::query()->lockForUpdate()->find($transactionId);
        if (!$tx) return;

        $sumD = (float) $tx->details()->sum('debit');
        $sumC = (float) $tx->details()->sum('credit');
        $rate = (float) ($tx->exchange_rate ?? 1);

        $tx->forceFill([
            'total_debit'       => round($sumD, 2),
            'total_credit'      => round($sumC, 2),
            'total_debit_base'  => round($sumD * $rate, 2),
            'total_credit_base' => round($sumC * $rate, 2),
        ])->save();
    }
}

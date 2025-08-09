<?php
// database/seeders/TransactionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Currency;
use App\Models\Account;
use App\Models\CostCenter;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            /** =========================
             *  1) Master minimal
             *  ========================= */
            // Currency (pastikan ada setidaknya 1)
            if (Currency::count() === 0) {
                // Kalau kamu punya seeder/fixture currency sendiri, boleh di-skip bagian ini.
                Currency::factory()->create(['code' => 'IDR', 'name' => 'Rupiah', 'symbol' => 'Rp']);
                Currency::factory()->create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$']);
                Currency::factory()->create(['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€']);
            }

            // Account (kode umum agar ledger enak dibaca)
            $mustHaveAccounts = [
                ['code' => '1001', 'name' => 'Cash'],
                ['code' => '1101', 'name' => 'Bank'],
                ['code' => '1201', 'name' => 'Accounts Receivable'],
                ['code' => '1301', 'name' => 'Prepaid Expense'],
                ['code' => '1401', 'name' => 'Fixed Assets'],
                ['code' => '2001', 'name' => 'Accounts Payable'],
                ['code' => '4001', 'name' => 'Sales Revenue'],
                ['code' => '5001', 'name' => 'COGS'],
                ['code' => '5101', 'name' => 'Salary Expense'],
                ['code' => '5201', 'name' => 'Rent Expense'],
                ['code' => '5301', 'name' => 'Utilities Expense'],
                ['code' => '5401', 'name' => 'Depreciation Expense'],
            ];

            foreach ($mustHaveAccounts as $a) {
                Account::firstOrCreate(['code' => $a['code']], ['name' => $a['name']]);
            }

            // Cost Center (opsional, tapi bermanfaat untuk contoh)
            if (CostCenter::count() === 0) {
                CostCenter::create(['name' => 'Head Office']);
                CostCenter::create(['name' => 'Sales']);
                CostCenter::create(['name' => 'Production']);
                CostCenter::create(['name' => 'R&D']);
            }

            /** =========================
             *  2) Generate transactions
             *  ========================= */
            $currencyIds = Currency::pluck('id')->all();

            // 24 posted, 12 draft (total 36) dengan lines 2..6
            $totalPosted = 24;
            $totalDraft  = 12;

            // Posted
            for ($i = 0; $i < $totalPosted; $i++) {
                Transaction::factory()
                    ->state([
                        'currency_id' => Arr::random($currencyIds),
                    ])
                    ->posted()
                    ->withLines(rand(2, 6))
                    ->create();
            }

            // Draft
            for ($i = 0; $i < $totalDraft; $i++) {
                Transaction::factory()
                    ->state([
                        'currency_id' => Arr::random($currencyIds),
                    ])
                    ->draft()
                    ->withLines(rand(2, 5))
                    ->create();
            }

            /** =========================
             *  3) Contoh jurnal reversal
             *  =========================
             *  Ambil 3 jurnal posted secara acak dan buat jurnal pembaliknya.
             */
            $toReverse = Transaction::posted()->inRandomOrder()->take(3)->get();

            foreach ($toReverse as $orig) {
                // Buat header reversal (tanggal +1 hari; periode otomatis mengikuti accessor/model)
                $revDate   = Carbon::parse($orig->date)->addDay();
                $exRate    = (float)($orig->exchange_rate ?: 1);

                $rev = Transaction::create([
                    'journal_no'        => Transaction::generateJournalNo(
                        (int)$revDate->format('Y'),
                        (int)$revDate->format('n')
                    ),
                    'reference'         => $orig->reference ? $orig->reference.'-REV' : null,
                    'description'       => 'Reversal of '.$orig->journal_no,
                    'date'              => $revDate->toDateString(),
                    'fiscal_year'       => (int)$revDate->format('Y'),
                    'fiscal_period'     => (int)$revDate->format('n'),
                    'currency_id'       => $orig->currency_id,
                    'exchange_rate'     => $exRate,
                    'total_debit'       => $orig->total_credit,           // dibalik
                    'total_credit'      => $orig->total_debit,            // dibalik
                    'total_debit_base'  => round($orig->total_credit * $exRate, 2),
                    'total_credit_base' => round($orig->total_debit  * $exRate, 2),
                    'status'            => 'posted',
                    'posted_at'         => now(),
                    'posted_by'         => 1,
                    'reversal_of_id'    => $orig->id,
                    'created_by'        => 1,
                ]);

                // Copy detail dengan debit/credit ditukar
                $details = $orig->details()->get();
                foreach ($details as $d) {
                    TransactionDetail::create([
                        'transaction_id'  => $rev->id,
                        'account_id'      => $d->account_id,
                        'debit'           => (float)$d->credit,
                        'credit'          => (float)$d->debit,
                        'cost_center_id'  => $d->cost_center_id,
                        'memo'            => 'REV: '.($d->memo ?? ''),
                    ]);
                }
            }
        });
    }
}

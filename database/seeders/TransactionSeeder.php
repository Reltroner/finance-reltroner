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
    public function run(): void
    {
        DB::transaction(function () {

            /**
             * =========================================================
             * 1) MASTER DATA (STRICT & DOMAIN-SAFE)
             * =========================================================
             */

            // ---- Currency (minimal set)
            if (Currency::count() === 0) {
                Currency::factory()->create(['code' => 'IDR', 'name' => 'Rupiah', 'symbol' => 'Rp']);
                Currency::factory()->create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$']);
                Currency::factory()->create(['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€']);
            }

            // ---- Chart of Accounts (WAJIB type)
            $chartOfAccounts = [
                ['code' => '1001', 'name' => 'Cash',                'type' => 'asset',     'normal_balance' => 'debit'],
                ['code' => '1101', 'name' => 'Bank',                'type' => 'asset',     'normal_balance' => 'debit'],
                ['code' => '1201', 'name' => 'Accounts Receivable', 'type' => 'asset',     'normal_balance' => 'debit'],
                ['code' => '1301', 'name' => 'Prepaid Expense',     'type' => 'asset',     'normal_balance' => 'debit'],
                ['code' => '1401', 'name' => 'Fixed Assets',        'type' => 'asset',     'normal_balance' => 'debit'],

                ['code' => '2001', 'name' => 'Accounts Payable',    'type' => 'liability', 'normal_balance' => 'credit'],

                ['code' => '4001', 'name' => 'Sales Revenue',       'type' => 'income',    'normal_balance' => 'credit'],

                ['code' => '5001', 'name' => 'COGS',                'type' => 'expense',   'normal_balance' => 'debit'],
                ['code' => '5101', 'name' => 'Salary Expense',      'type' => 'expense',   'normal_balance' => 'debit'],
                ['code' => '5201', 'name' => 'Rent Expense',        'type' => 'expense',   'normal_balance' => 'debit'],
                ['code' => '5301', 'name' => 'Utilities Expense',   'type' => 'expense',   'normal_balance' => 'debit'],
                ['code' => '5401', 'name' => 'Depreciation Expense','type' => 'expense',   'normal_balance' => 'debit'],
            ];

            foreach ($chartOfAccounts as $acc) {
                Account::firstOrCreate(
                    ['code' => $acc['code']],
                    [
                        'name'           => $acc['name'],
                        'type'           => $acc['type'],
                        'normal_balance' => $acc['normal_balance'],
                        'is_active'      => true,
                    ]
                );
            }

            // ---- Cost Centers
            if (CostCenter::count() === 0) {
                CostCenter::insert([
                    ['name' => 'Head Office'],
                    ['name' => 'Sales'],
                    ['name' => 'Production'],
                    ['name' => 'R&D'],
                ]);
            }

            /**
             * =========================================================
             * 2) GENERATE TRANSACTIONS (FACTORY-DRIVEN)
             * =========================================================
             */

            $currencyIds = Currency::pluck('id')->all();

            $postedCount = 24;
            $draftCount  = 12;

            // ---- Posted Transactions
            for ($i = 0; $i < $postedCount; $i++) {
                Transaction::factory()
                    ->state([
                        'currency_id' => Arr::random($currencyIds),
                    ])
                    ->posted()
                    ->withLines(rand(2, 6))
                    ->create();
            }

            // ---- Draft Transactions
            for ($i = 0; $i < $draftCount; $i++) {
                Transaction::factory()
                    ->state([
                        'currency_id' => Arr::random($currencyIds),
                    ])
                    ->draft()
                    ->withLines(rand(2, 5))
                    ->create();
            }

            /**
             * =========================================================
             * 3) REVERSAL JOURNAL (AKUNTANSI VALID)
             * =========================================================
             */

            $toReverse = Transaction::posted()
                ->inRandomOrder()
                ->take(3)
                ->get();

            foreach ($toReverse as $orig) {
                $revDate = Carbon::parse($orig->date)->addDay();
                $rate    = (float) ($orig->exchange_rate ?: 1);

                $rev = Transaction::create([
                    'journal_no'        => Transaction::generateJournalNo(
                        (int) $revDate->format('Y'),
                        (int) $revDate->format('n')
                    ),
                    'reference'         => $orig->reference ? $orig->reference . '-REV' : null,
                    'description'       => 'Reversal of ' . $orig->journal_no,
                    'date'              => $revDate->toDateString(),
                    'fiscal_year'       => (int) $revDate->format('Y'),
                    'fiscal_period'     => (int) $revDate->format('n'),
                    'currency_id'       => $orig->currency_id,
                    'exchange_rate'     => $rate,
                    'total_debit'       => $orig->total_credit,
                    'total_credit'      => $orig->total_debit,
                    'total_debit_base'  => round($orig->total_credit * $rate, 2),
                    'total_credit_base' => round($orig->total_debit  * $rate, 2),
                    'status'            => 'posted',
                    'posted_at'         => now(),
                    'posted_by'         => 1,
                    'reversal_of_id'    => $orig->id,
                    'created_by'        => 1,
                ]);

                foreach ($orig->details as $d) {
                    TransactionDetail::create([
                        'transaction_id' => $rev->id,
                        'account_id'     => $d->account_id,
                        'debit'          => (float) $d->credit,
                        'credit'         => (float) $d->debit,
                        'cost_center_id' => $d->cost_center_id,
                        'memo'           => 'REV: ' . ($d->memo ?? ''),
                    ]);
                }
            }
        });
    }
}

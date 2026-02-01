<?php
// database/seeders/TransactionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

use App\Models\Currency;
use App\Models\Account;
use App\Models\CostCenter;
use App\Models\Transaction;

use App\Services\Accounting\TransactionService;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /**
             * =========================================================
             * 1) MASTER DATA (DOMAIN SAFE)
             * =========================================================
             */

            if (Currency::count() === 0) {
                Currency::factory()->create(['code' => 'IDR', 'name' => 'Rupiah', 'symbol' => 'Rp']);
                Currency::factory()->create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$']);
                Currency::factory()->create(['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€']);
            }

            $chartOfAccounts = [
                ['code' => '1001', 'name' => 'Cash',                'type' => 'asset',     'normal_balance' => 'debit'],
                ['code' => '1101', 'name' => 'Bank',                'type' => 'asset',     'normal_balance' => 'debit'],
                ['code' => '1201', 'name' => 'Accounts Receivable', 'type' => 'asset',     'normal_balance' => 'debit'],
                ['code' => '2001', 'name' => 'Accounts Payable',    'type' => 'liability', 'normal_balance' => 'credit'],
                ['code' => '4001', 'name' => 'Sales Revenue',       'type' => 'income',    'normal_balance' => 'credit'],
                ['code' => '5101', 'name' => 'Salary Expense',      'type' => 'expense',   'normal_balance' => 'debit'],
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

            if (CostCenter::count() === 0) {
                CostCenter::insert([
                    ['name' => 'Head Office'],
                    ['name' => 'Sales'],
                    ['name' => 'Production'],
                ]);
            }

            /**
             * =========================================================
             * 2) TRANSACTION GENERATION (SERVICE-DRIVEN)
             * =========================================================
             */

            $txService = app(TransactionService::class);

            $currencyIds = Currency::pluck('id')->all();
            $accounts    = Account::all()->keyBy('code');

            $systemUserId = 1;

            // ---- Posted Transactions
            for ($i = 0; $i < 24; $i++) {
                $txService->create([
                    'date'        => now()->subDays(rand(1, 60))->toDateString(),
                    'currency_id' => Arr::random($currencyIds),
                    'reference'   => 'SEED-POSTED-' . $i,
                    'description' => 'Seeded posted transaction',
                    'type'        => Transaction::TYPE_GENERAL,
                    'details'     => [
                        [
                            'account_id' => $accounts['1001']->id,
                            'debit'      => rand(100, 500),
                        ],
                        [
                            'account_id' => $accounts['4001']->id,
                            'credit'     => rand(100, 500),
                        ],
                    ],
                ], $systemUserId, autoPost: true);
            }

            // ---- Draft Transactions
            for ($i = 0; $i < 12; $i++) {
                $txService->create([
                    'date'        => now()->subDays(rand(1, 30))->toDateString(),
                    'currency_id' => Arr::random($currencyIds),
                    'reference'   => 'SEED-DRAFT-' . $i,
                    'description' => 'Seeded draft transaction',
                    'type'        => Transaction::TYPE_GENERAL,
                    'details'     => [
                        [
                            'account_id' => $accounts['5101']->id,
                            'debit'      => rand(50, 300),
                        ],
                        [
                            'account_id' => $accounts['1001']->id,
                            'credit'     => rand(50, 300),
                        ],
                    ],
                ], $systemUserId, autoPost: false);
            }

            /**
             * =========================================================
             * 3) REVERSAL JOURNAL (DOMAIN VALID)
             * =========================================================
             */

            $toReverse = Transaction::posted()
                ->whereNull('reversal_of_id')
                ->inRandomOrder()
                ->take(3)
                ->get();

            foreach ($toReverse as $orig) {
                $txService->reverse(
                    original: $orig,
                    date: Carbon::parse($orig->date)->addDay()->toDateString(),
                    userId: $systemUserId
                );
            }
        });
    }
}

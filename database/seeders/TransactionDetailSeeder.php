<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\TransactionDetail;

class TransactionDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactions = Transaction::all();
        $accounts = Account::pluck('id')->toArray();

        foreach ($transactions as $txn) {
            TransactionDetail::factory(2)->create([
                'transaction_id' => $txn->id,
                'account_id' => $accounts[array_rand($accounts)]
            ]);
        }
    }
}

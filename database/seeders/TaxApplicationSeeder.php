<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TaxApplication;
use App\Models\Tax;
use App\Models\Transaction;

class TaxApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxes = Tax::pluck('id')->toArray();
        $transactions = Transaction::pluck('id')->toArray();

        foreach ($transactions as $txn_id) {
            TaxApplication::factory()->create([
                'tax_id' => $taxes[array_rand($taxes)],
                'transaction_id' => $txn_id
            ]);
        }
    }
}

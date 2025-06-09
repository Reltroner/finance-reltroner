<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\Transaction;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $invoices = Invoice::pluck('id')->toArray();
        $vendors = Vendor::pluck('id')->toArray();
        $customers = Customer::pluck('id')->toArray();
        $transactions = Transaction::pluck('id')->toArray();

        Payment::factory(10)->create([
            'invoice_id' => fn() => $invoices[array_rand($invoices)],
            'vendor_id' => fn() => $vendors[array_rand($vendors)],
            'customer_id' => fn() => $customers[array_rand($customers)],
            'transaction_id' => fn() => $transactions[array_rand($transactions)],
        ]);
    }
}

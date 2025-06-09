<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Customer;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::pluck('id')->toArray();
        Invoice::factory(10)->create([
            'customer_id' => function() use ($customers) {
                return $customers[array_rand($customers)];
            }
        ]);
    }
}

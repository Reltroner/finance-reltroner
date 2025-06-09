<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class,
            AccountSeeder::class,
            CustomerSeeder::class,
            VendorSeeder::class,
            TaxSeeder::class,
            CostCenterSeeder::class,
            TransactionSeeder::class,
            TransactionDetailSeeder::class,
            InvoiceSeeder::class,
            PaymentSeeder::class,
            BudgetSeeder::class,
            AttachmentSeeder::class,
            TaxApplicationSeeder::class,
            AuditLogSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tax;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tax::factory()->create(['name' => 'VAT', 'percentage' => 10]);
        Tax::factory()->create(['name' => 'Service Tax', 'percentage' => 5]);
        Tax::factory(3)->create();
    }
}

<?php
// database/seeders/AccountSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        Account::firstOrCreate(
            ['code' => '1001'],
            [
                'name'           => 'Cash',
                'type'           => 'asset',
                'normal_balance' => 'debit',
                'is_active'      => true,
            ]
        );
    }
}

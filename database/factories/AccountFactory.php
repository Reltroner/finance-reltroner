<?php
// database/factories/AccountFactory.php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement([
            'asset',
            'liability',
            'equity',
            'income',
            'expense',
        ]);

        $normalBalance = in_array($type, ['asset', 'expense'])
            ? 'debit'
            : 'credit';

        return [
            'code'           => $this->faker->unique()->numerify('###.##'),
            'name'           => $this->faker->words(2, true),
            'type'           => $type,
            'normal_balance' => $normalBalance,
            'parent_id'      => null,
            'is_active'      => true,
        ];
    }
}

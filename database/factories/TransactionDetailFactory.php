<?php

namespace Database\Factories;

use App\Models\TransactionDetail;
use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionDetail>
 */
class TransactionDetailFactory extends Factory
{
    protected $model = TransactionDetail::class;

    public function definition()
    {
        return [
            'transaction_id' => Transaction::factory(),
            'account_id' => Account::factory(),
            'description' => $this->faker->sentence(),
            'debit' => $this->faker->randomFloat(2, 100, 5000),
            'credit' => $this->faker->randomFloat(2, 100, 5000),
            'cost_center_id' => null,
        ];
    }
}

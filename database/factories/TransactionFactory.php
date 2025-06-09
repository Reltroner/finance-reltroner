<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'reference' => $this->faker->unique()->numerify('TXN#######'),
            'description' => $this->faker->sentence(),
            'date' => $this->faker->date(),
            'currency_id' => Currency::factory(),
            'total_debit' => $this->faker->randomFloat(2, 1000, 50000),
            'total_credit' => $this->faker->randomFloat(2, 1000, 50000),
            'created_by' => 1,
        ];
    }
}

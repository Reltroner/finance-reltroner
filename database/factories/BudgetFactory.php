<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    public function definition()
    {
        return [
            'account_id' => Account::factory(),
            'year' => $this->faker->year(),
            'month' => $this->faker->numberBetween(1, 12),
            'amount' => $this->faker->randomFloat(2, 1000, 50000),
            'actual' => $this->faker->randomFloat(2, 1000, 50000),
        ];
    }
}

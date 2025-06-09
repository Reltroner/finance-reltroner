<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Currency>
 */
class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->currencyCode(),
            'name' => $this->faker->country(),
            'symbol' => $this->faker->currencyCode(),
            'rate' => $this->faker->randomFloat(4, 0.5, 2.0),
        ];
    }
}

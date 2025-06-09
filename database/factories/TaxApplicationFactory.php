<?php

namespace Database\Factories;

use App\Models\TaxApplication;
use App\Models\Tax;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaxApplication>
 */
class TaxApplicationFactory extends Factory
{
    protected $model = TaxApplication::class;

    public function definition()
    {
        return [
            'tax_id' => Tax::factory(),
            'transaction_id' => Transaction::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 5000),
        ];
    }
}

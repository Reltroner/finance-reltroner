<?php

namespace Database\Factories;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tax>
 */
class TaxFactory extends Factory
{
    protected $model = Tax::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['VAT', 'PPN', 'GST', 'Service Tax']),
            'percentage' => $this->faker->randomFloat(2, 5, 20),
            'description' => $this->faker->sentence(),
        ];
    }
}

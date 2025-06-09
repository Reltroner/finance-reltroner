<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        return [
            'invoice_number' => $this->faker->unique()->bothify('INV-#####'),
            'customer_id' => Customer::factory(),
            'date' => $this->faker->date(),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'total_amount' => $this->faker->randomFloat(2, 1000, 20000),
            'status' => $this->faker->randomElement(['draft', 'sent', 'paid', 'overdue', 'cancelled']),
            'description' => $this->faker->sentence(),
        ];
    }
}

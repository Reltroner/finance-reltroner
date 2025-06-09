<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'invoice_id' => Invoice::factory(),
            'vendor_id' => Vendor::factory(),
            'customer_id' => Customer::factory(),
            'transaction_id' => Transaction::factory(),
            'amount' => $this->faker->randomFloat(2, 1000, 20000),
            'date' => $this->faker->date(),
            'payment_method' => $this->faker->randomElement(['bank transfer', 'cash', 'credit card']),
            'status' => $this->faker->randomElement(['pending', 'cleared', 'failed']),
            'description' => $this->faker->sentence(),
        ];
    }
}

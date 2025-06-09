<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attachment>
 */
class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition()
    {
        return [
            'transaction_id' => Transaction::factory(),
            'file_path' => 'uploads/' . $this->faker->uuid . '.pdf',
            'file_name' => $this->faker->word() . '.pdf',
            'uploaded_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition()
    {
        $type = $this->faker->randomElement(['asset', 'liability', 'equity', 'income', 'expense']);
        return [
            'code' => $this->faker->unique()->numerify('###.##'),
            'name' => $this->faker->words(2, true),
            'type' => $type,
            'parent_id' => null, // Bisa dihubungkan nanti secara manual
            'is_active' => true,
        ];
    }
}

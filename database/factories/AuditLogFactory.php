<?php

namespace Database\Factories;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition()
    {
        $table = $this->faker->randomElement([
            'accounts', 'transactions', 'customers', 'vendors', 'invoices'
        ]);
        $action = $this->faker->randomElement(['created', 'updated', 'deleted']);

        return [
            'table_name' => $table,
            'record_id' => $this->faker->randomNumber(5, true),
            'action' => $action,
            'data_old' => json_encode(['sample' => 'old']),
            'data_new' => json_encode(['sample' => 'new']),
            'changed_by' => 1,
            'changed_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}

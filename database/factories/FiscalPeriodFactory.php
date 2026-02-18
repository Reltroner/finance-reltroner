<?php
// database/factories/FiscalPeriodFactory.php
namespace Database\Factories;

use App\Models\FiscalPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class FiscalPeriodFactory extends Factory
{
    protected $model = FiscalPeriod::class;

    public function definition(): array
    {
        return [
            'year' => 2025,
            'period' => 1,
            'status' => 'open',
            'closed_at' => null,
            'locked_at' => null,
            'closed_by' => null,
            'locked_by' => null,
        ];
    }

    public function closed(): self
    {
        return $this->state(fn () => [
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    public function locked(): self
    {
        return $this->state(fn () => [
            'status' => 'locked',
            'locked_at' => now(),
        ]);
    }
}

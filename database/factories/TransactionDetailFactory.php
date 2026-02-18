<?php
// database/factories/TransactionDetailFactory.php
namespace Database\Factories;

use App\Models\TransactionDetail;
use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionDetailFactory extends Factory
{
    protected $model = TransactionDetail::class;

    public function definition(): array
    {
        $isDebit = $this->faker->boolean(65);
        $amount  = $this->faker->randomFloat(2, 10, 5000);

        return [
            'transaction_id' => Transaction::factory(),

            // 🔥 Unique per factory instance
            'line_no' => $this->faker->unique()->numberBetween(1, 10000),

            'account_id' => Account::factory(),

            'debit'  => $isDebit ? $amount : 0,
            'credit' => $isDebit ? 0 : $amount,

            'cost_center_id' => null,
            'memo' => null,
        ];
    }

    public function debit(float $amount = null): self
    {
        return $this->state(fn () => [
            'debit'  => $amount ?? 100,
            'credit' => 0,
        ]);
    }

    public function credit(float $amount = null): self
    {
        return $this->state(fn () => [
            'debit'  => 0,
            'credit' => $amount ?? 100,
        ]);
    }

    public function lineNo(int $n): self
    {
        return $this->state(fn () => [
            'line_no' => max(1, $n),
        ]);
    }

    public function forTransaction(Transaction $tx): self
    {
        return $this->state(fn () => [
            'transaction_id' => $tx->id,
            // Generate safe incremental using current count
            'line_no' => TransactionDetail::query()
                ->where('transaction_id', $tx->id)
                ->count() + 1,
        ]);
    }
}

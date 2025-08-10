<?php

namespace Database\Factories;

use App\Models\TransactionDetail;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\CostCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories.Factory<\App\Models\TransactionDetail>
 */
class TransactionDetailFactory extends Factory
{
    protected $model = TransactionDetail::class;

    /**
     * Menjaga urutan line_no per transaction sementara di memory (untuk factory usage).
     */
    protected static array $seqByTx = [];

    protected function nextLineNoFor(int $transactionId): int
    {
        self::$seqByTx[$transactionId] = (self::$seqByTx[$transactionId] ?? 0) + 1;
        return self::$seqByTx[$transactionId];
    }

    public function definition(): array
    {
        // Pilih tipe baris: lebih sering debit
        $isDebit = $this->faker->boolean(65);
        $amount  = $this->faker->randomFloat(2, 10, 5000);

        // Jika transaction_id belum diset (dipanggil langsung), factory Transaction akan dibuat otomatis.
        // line_no sementara diisi 1; gunakan state forTransaction() saat attach ke jurnal tertentu agar urutan rapi.
        return [
            'transaction_id' => Transaction::factory(),
            'line_no'        => 1,

            'account_id'     => Account::factory(),

            'debit'          => $isDebit ? $amount : 0,
            'credit'         => $isDebit ? 0 : $amount,

            'cost_center_id' => $this->faker->optional(0.4)->randomElement(
                CostCenter::query()->inRandomOrder()->limit(1)->pluck('id')->all()
            ),
            'memo'           => $this->faker->optional(0.6)->sentence(3),
        ];
    }

    /**
     * State: paksa baris debit (credit = 0).
     */
    public function debit(float $amount = null): self
    {
        return $this->state(function () use ($amount) {
            $amt = $amount ?? $this->faker->randomFloat(2, 10, 5000);
            return ['debit' => $amt, 'credit' => 0];
        });
    }

    /**
     * State: paksa baris credit (debit = 0).
     */
    public function credit(float $amount = null): self
    {
        return $this->state(function () use ($amount) {
            $amt = $amount ?? $this->faker->randomFloat(2, 10, 5000);
            return ['debit' => 0, 'credit' => $amt];
        });
    }

    /**
     * State: set line_no spesifik.
     */
    public function lineNo(int $n): self
    {
        return $this->state(fn() => ['line_no' => max(1, $n)]);
    }

    /**
     * State: attach ke transaksi tertentu + auto-increment line_no.
     */
    public function forTransaction(Transaction $tx): self
    {
        $next = $this->nextLineNoFor($tx->id);
        return $this->state(fn() => [
            'transaction_id' => $tx->id,
            'line_no'        => $next,
        ]);
    }
}

<?php
// database/factories/TransactionFactory.php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Currency;
use App\Models\TransactionDetail;
use App\Models\Account;
use App\Models\CostCenter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Sederhana: sequence lokal per tahun-periode untuk journal_no.
     */
    protected static array $seqPerPeriod = [];

    protected function nextJournalNo(int $year, int $period): string
    {
        $key = $year.'-'.$period;
        self::$seqPerPeriod[$key] = (self::$seqPerPeriod[$key] ?? 0) + 1;
        $seq = str_pad((string) self::$seqPerPeriod[$key], 6, '0', STR_PAD_LEFT);
        return 'JV-' . $year . '-' . str_pad((string)$period, 2, '0', STR_PAD_LEFT) . '-' . $seq;
    }

    public function definition(): array
    {
        // Tanggal 12 bulan terakhir
        $date = Carbon::instance($this->faker->dateTimeBetween('-12 months', 'now'));
        $year = (int) $date->format('Y');
        $per  = (int) $date->format('n');

        // Kurs & total (balanced)
        $exchange = $this->faker->randomFloat(10, 0.8000000000, 1.5000000000);
        $total    = $this->faker->randomFloat(2, 500, 25000); // debit = credit = total

        // Status random ringan (lebih banyak draft)
        $status   = $this->faker->randomElement(['draft','draft','draft','posted']);

        return [
            // Identitas & periode
            'journal_no'        => $this->nextJournalNo($year, $per),
            'reference'         => $this->faker->unique()->bothify('REF-#####'),
            'description'       => $this->faker->sentence(),
            'date'              => $date->toDateString(),
            'fiscal_year'       => $year,
            'fiscal_period'     => $per,

            // Currency & totals
            'currency_id'       => Currency::factory(),
            'exchange_rate'     => $exchange,
            'total_debit'       => $total,
            'total_credit'      => $total,
            'total_debit_base'  => round($total * $exchange, 2),
            'total_credit_base' => round($total * $exchange, 2),

            // Status
            'status'            => $status,
            'posted_at'         => $status === 'posted' ? $date->copy()->addMinutes(rand(0, 600)) : null,
            'posted_by'         => $status === 'posted' ? 1 : null,
            'voided_at'         => null,
            'voided_by'         => null,
            'reversal_of_id'    => null,

            // Metadata
            'created_by'        => 1,
        ];
    }

    /**
     * State: Draft
     */
    public function draft(): self
    {
        return $this->state(fn(array $attrs) => [
            'status'    => 'draft',
            'posted_at' => null,
            'posted_by' => null,
            'voided_at' => null,
            'voided_by' => null,
        ]);
    }

    /**
     * State: Posted
     */
    public function posted(): self
    {
        return $this->state(function (array $attrs) {
            $ts = Carbon::parse($attrs['date'] ?? now())->addMinutes(rand(1, 600));
            return [
                'status'    => 'posted',
                'posted_at' => $ts,
                'posted_by' => 1,
                'voided_at' => null,
                'voided_by' => null,
            ];
        });
    }

    /**
     * State: Voided
     */
    public function voided(): self
    {
        return $this->state(function (array $attrs) {
            $ts = Carbon::parse($attrs['date'] ?? now())->addMinutes(rand(1, 600));
            return [
                'status'    => 'voided',
                'posted_at' => null,
                'posted_by' => null,
                'voided_at' => $ts,
                'voided_by' => 1,
            ];
        });
    }

    /**
     * Tambahkan detail baris yang balanced.
     * Contoh: Transaction::factory()->withLines(4)->create();
     */
    public function withLines(int $lines = 4): self
    {
        $lines = max(2, $lines); // minimal 2 baris

        return $this->afterCreating(function (Transaction $tx) use ($lines) {
            // Ambil total & bagi ke beberapa baris
            $remainingDebit  = (float) $tx->total_debit;
            $remainingCredit = (float) $tx->total_credit;

            // Pastikan ada account & cost center (fallback factory jika kosong)
            $accounts    = Account::query()->inRandomOrder()->limit($lines)->pluck('id');
            if ($accounts->count() < $lines) {
                // create sisa account via factory jika belum ada
                for ($i = $accounts->count(); $i < $lines; $i++) {
                    $accounts->push(Account::factory()->create()->id);
                }
            }
            $costcenters = CostCenter::query()->inRandomOrder()->limit($lines)->pluck('id');

            // Generate n-1 baris acak, baris terakhir sebagai balancing
            for ($i = 0; $i < $lines; $i++) {
                $isDebit = ($i % 2 === 0); // selang-seling
                $amount  = $i === $lines - 1
                    ? ($isDebit ? $remainingDebit : $remainingCredit)
                    : round($this->faker->randomFloat(2, 1, max(1, ($tx->total_debit / $lines))), 2);

                if ($isDebit) {
                    $amount = min($amount, $remainingDebit);
                    $debit = $amount;
                    $credit = 0;
                    $remainingDebit  = round($remainingDebit  - $debit, 2);
                } else {
                    $amount = min($amount, $remainingCredit);
                    $debit = 0;
                    $credit = $amount;
                    $remainingCredit = round($remainingCredit - $credit, 2);
                }

                // Jika ini baris terakhir dan masih ada selisih, pasangkan agar balance
                if ($i === $lines - 1) {
                    if ($isDebit && $remainingCredit > 0) {
                        // ubah baris terakhir jadi credit agar balance
                        $credit = $remainingCredit;
                        $debit  = 0;
                        $remainingCredit = 0;
                    } elseif (!$isDebit && $remainingDebit > 0) {
                        $debit  = $remainingDebit;
                        $credit = 0;
                        $remainingDebit = 0;
                    }
                }

                TransactionDetail::create([
                    'transaction_id'  => $tx->id,
                    'account_id'      => $accounts[$i],
                    'debit'           => $debit,
                    'credit'          => $credit,
                    'cost_center_id'  => $costcenters[$i] ?? null,
                    'memo'            => $this->faker->optional(0.6)->sentence(3),
                ]);
            }

            // Safety: hitung ulang total dari detail (kalau mau konsisten penuh)
            $sumD = round($tx->details()->sum('debit'), 2);
            $sumC = round($tx->details()->sum('credit'), 2);
            DB::table('transactions')
                ->where('id', $tx->id)
                ->update([
                    'total_debit'       => $sumD,
                    'total_credit'      => $sumC,
                    'total_debit_base'  => round($sumD * (float)$tx->exchange_rate, 2),
                    'total_credit_base' => round($sumC * (float)$tx->exchange_rate, 2),
                    'updated_at'        => now(),
                ]);

            // sinkronkan model di memori (opsional agar objek $tx up-to-date)
            $tx->refresh();
        });
    }
}

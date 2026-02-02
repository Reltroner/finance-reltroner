<?php
// app/Services/Accounting/TransactionService.php

namespace App\Services\Accounting;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TransactionService
{
    public function __construct(
        protected TransactionGuard $guard
    ) {}

    /**
     * =========================================================
     * CREATE (Single Source of Truth)
     * =========================================================
     */
    public function create(
        array $data,
        int $userId,
        bool $autoPost = false
    ): Transaction {
        return DB::transaction(function () use ($data, $userId, $autoPost) {

            $date   = Carbon::parse($data['date']);
            $year   = (int) $date->format('Y');
            $period = (int) $date->format('n');

            // ---- GUARDS
            $this->guard->assertPeriodWritable($year, $period, $data['type'] ?? null);
            $this->guard->assertAccountUsable($data['details'], $data['type'] ?? null);

            $lines = collect($data['details']);

            $sumDebit  = round($lines->sum(fn ($l) => (float) ($l['debit']  ?? 0)), 2);
            $sumCredit = round($lines->sum(fn ($l) => (float) ($l['credit'] ?? 0)), 2);

            $exchangeRate = (float) ($data['exchange_rate'] ?? 1);

            $tx = Transaction::create([
                'journal_no'        => Transaction::generateJournalNo($year, $period),
                'reference'         => $data['reference']   ?? null,
                'description'       => $data['description'] ?? null,
                'date'              => $date,
                'fiscal_year'       => $year,
                'fiscal_period'     => $period,
                'currency_id'       => $data['currency_id'],
                'exchange_rate'     => $exchangeRate,
                'total_debit'       => $sumDebit,
                'total_credit'      => $sumCredit,
                'total_debit_base'  => round($sumDebit  * $exchangeRate, 2),
                'total_credit_base' => round($sumCredit * $exchangeRate, 2),
                'status'            => $autoPost ? 'posted' : ($data['status'] ?? 'draft'),
                'type'              => $data['type'] ?? Transaction::TYPE_GENERAL,
                'created_by'        => $userId,
            ]);

            $tx->details()->createMany(
                $lines->values()->map(fn ($l, $i) => [
                    'line_no'        => $i + 1,
                    'account_id'     => $l['account_id'],
                    'debit'          => (float) ($l['debit']  ?? 0),
                    'credit'         => (float) ($l['credit'] ?? 0),
                    'cost_center_id' => $l['cost_center_id'] ?? null,
                    'memo'           => $l['memo'] ?? null,
                ])->all()
            );

            if ($autoPost) {
                $tx->markPosted($userId);
            }

            return $tx;
        });
    }

    /**
     * =========================================================
     * UPDATE (Controlled Mutation)
     * =========================================================
     */
    public function update(
        Transaction $tx,
        array $data,
        int $userId
    ): Transaction {
        return DB::transaction(function () use ($tx, $data, $userId) {

            $this->guard->assertEditable($tx, $data);

            if (isset($data['date'])) {
                $date = Carbon::parse($data['date']);
                $tx->date          = $date;
                $tx->fiscal_year   = (int) $date->format('Y');
                $tx->fiscal_period = (int) $date->format('n');

                $this->guard->assertPeriodWritable(
                    $tx->fiscal_year,
                    $tx->fiscal_period,
                    $data['type'] ?? $tx->type
                );
            }

            $tx->fill(collect($data)->except('details')->all());
            $tx->save(); // Observer still applies

            if (isset($data['details'])) {
                $this->guard->assertAccountUsable(
                    $data['details'],
                    $data['type'] ?? $tx->type
                );

                $lines = collect($data['details']);

                $sumDebit  = round($lines->sum(fn ($l) => (float) ($l['debit']  ?? 0)), 2);
                $sumCredit = round($lines->sum(fn ($l) => (float) ($l['credit'] ?? 0)), 2);

                $rate = (float) ($data['exchange_rate'] ?? $tx->exchange_rate ?? 1);

                $tx->update([
                    'total_debit'       => $sumDebit,
                    'total_credit'      => $sumCredit,
                    'total_debit_base'  => round($sumDebit  * $rate, 2),
                    'total_credit_base' => round($sumCredit * $rate, 2),
                ]);

                $tx->details()->delete();
                $tx->details()->createMany(
                    $lines->values()->map(fn ($l, $i) => [
                        'line_no'        => $i + 1,
                        'account_id'     => $l['account_id'],
                        'debit'          => (float) ($l['debit']  ?? 0),
                        'credit'         => (float) ($l['credit'] ?? 0),
                        'cost_center_id' => $l['cost_center_id'] ?? null,
                        'memo'           => $l['memo'] ?? null,
                    ])->all()
                );
            }

            if (($data['status'] ?? null) === 'posted' && !$tx->posted_at) {
                $tx->markPosted($userId);
            }

            if (($data['status'] ?? null) === 'voided' && !$tx->voided_at) {
                $tx->markVoided($userId);
            }

            return $tx;
        });
    }

    /**
     * =========================================================
     * DELETE (Soft, Guarded)
     * =========================================================
     */
    public function delete(Transaction $tx): void
    {
        DB::transaction(function () use ($tx) {
            $this->guard->assertDeletable($tx);
            $tx->details()->delete();
            $tx->delete();
        });
    }

    /**
     * =========================================================
     * REVERSAL (Explicit Domain Operation)
     * =========================================================
     */
    public function reverse(
        Transaction $original,
        string $date,
        int $userId
    ): Transaction {
        $lines = $original->details->map(fn ($d) => [
            'account_id'     => $d->account_id,
            'debit'          => $d->credit,
            'credit'         => $d->debit,
            'cost_center_id' => $d->cost_center_id,
            'memo'           => 'REV: ' . ($d->memo ?? ''),
        ])->all();

        return $this->create([
            'date'        => $date,
            'currency_id' => $original->currency_id,
            'reference'   => $original->reference ? $original->reference . '-REV' : null,
            'description' => 'Reversal of ' . $original->journal_no,
            'type'        => Transaction::TYPE_SYSTEM_ADJUSTMENT,
            'details'     => $lines,
        ], $userId, autoPost: true);
    }
}

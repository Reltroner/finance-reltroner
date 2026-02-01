<?php
// app/Services/Accounting/TransactionService.php
namespace App\Services\Accounting;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TransactionService
{
    /**
     * Create a new transaction (single source of truth).
     *
     * Fiscal Period Lock:
     * - ENFORCED by TransactionObserver (STEP 5.2B.4)
     */
    public function create(array $data, int $userId): Transaction
    {
        return DB::transaction(function () use ($data, $userId) {

            $date   = Carbon::parse($data['date']);
            $year   = (int) $date->format('Y');
            $period = (int) $date->format('n');

            $lines = collect($data['details']);

            $sumDebit  = round($lines->sum(fn ($l) => (float) ($l['debit'] ?? 0)), 2);
            $sumCredit = round($lines->sum(fn ($l) => (float) ($l['credit'] ?? 0)), 2);

            $exchangeRate = isset($data['exchange_rate'])
                ? (float) $data['exchange_rate']
                : 1.0;

            $sumDebitBase  = round($sumDebit  * $exchangeRate, 2);
            $sumCreditBase = round($sumCredit * $exchangeRate, 2);

            $tx = Transaction::create([
                'journal_no'        => Transaction::generateJournalNo($year, $period),
                'reference'         => $data['reference']     ?? null,
                'description'       => $data['description']   ?? null,
                'date'              => $date,
                'fiscal_year'       => $year,
                'fiscal_period'     => $period,
                'currency_id'       => $data['currency_id'],
                'exchange_rate'     => $exchangeRate,
                'total_debit'       => $sumDebit,
                'total_credit'      => $sumCredit,
                'total_debit_base'  => $sumDebitBase,
                'total_credit_base' => $sumCreditBase,
                'status'            => $data['status'] ?? 'draft',
                'type'              => $data['type']   ?? Transaction::TYPE_GENERAL,
                'created_by'        => $userId,
            ]);

            $payload = $lines->values()->map(function ($l, $i) {
                return [
                    'line_no'        => $i + 1,
                    'account_id'     => $l['account_id'],
                    'debit'          => (float) ($l['debit']  ?? 0),
                    'credit'         => (float) ($l['credit'] ?? 0),
                    'cost_center_id' => $l['cost_center_id'] ?? null,
                    'memo'           => $l['memo'] ?? null,
                ];
            })->all();

            $tx->details()->createMany($payload);

            if ($tx->status === 'posted') {
                $tx->markPosted($userId);
            }

            return $tx;
        });
    }

    /**
     * Update existing transaction.
     *
     * Immutability + Fiscal Period Lock:
     * - Enforced by TransactionObserver
     * - Enforced by TransactionPolicy / Controller guard
     */
    public function update(Transaction $tx, array $data, int $userId): Transaction
    {
        return DB::transaction(function () use ($tx, $data, $userId) {

            if (isset($data['date'])) {
                $date = Carbon::parse($data['date']);
                $tx->fiscal_year   = (int) $date->format('Y');
                $tx->fiscal_period = (int) $date->format('n');
                $tx->date          = $date;
            }

            $tx->fill(collect($data)->except('details')->all());
            $tx->save(); // ← Observer fires here

            if (isset($data['details'])) {

                $lines = collect($data['details']);

                $sumDebit  = round($lines->sum(fn ($l) => (float) ($l['debit'] ?? 0)), 2);
                $sumCredit = round($lines->sum(fn ($l) => (float) ($l['credit'] ?? 0)), 2);

                $exchangeRate = isset($data['exchange_rate'])
                    ? (float) $data['exchange_rate']
                    : (float) ($tx->exchange_rate ?? 1);

                $tx->update([
                    'total_debit'       => $sumDebit,
                    'total_credit'      => $sumCredit,
                    'total_debit_base'  => round($sumDebit  * $exchangeRate, 2),
                    'total_credit_base' => round($sumCredit * $exchangeRate, 2),
                ]);

                $tx->details()->delete();

                $payload = $lines->values()->map(function ($l, $i) {
                    return [
                        'line_no'        => $i + 1,
                        'account_id'     => $l['account_id'],
                        'debit'          => (float) ($l['debit']  ?? 0),
                        'credit'         => (float) ($l['credit'] ?? 0),
                        'cost_center_id' => $l['cost_center_id'] ?? null,
                        'memo'           => $l['memo'] ?? null,
                    ];
                })->all();

                $tx->details()->createMany($payload);
            }

            if (
                isset($data['status'])
                && $data['status'] === 'posted'
                && !$tx->posted_at
            ) {
                $tx->markPosted($userId);
            }

            if (
                isset($data['status'])
                && $data['status'] === 'voided'
                && !$tx->voided_at
            ) {
                $tx->markVoided($userId);
            }

            return $tx;
        });
    }

    /**
     * Delete transaction.
     *
     * Fiscal Period Lock:
     * - Observer still applies (delete triggers saving/deleting).
     */
    public function delete(Transaction $tx): void
    {
        DB::transaction(function () use ($tx) {
            $tx->details()->delete();
            $tx->delete(); // Observer applies here
        });
    }
}

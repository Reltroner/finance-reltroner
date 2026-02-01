<?php
// app/Services/Accounting/PeriodClosingService.php

namespace App\Services\Accounting;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use RuntimeException;

class PeriodClosingService
{
    /**
     * Close accounting period and transfer P&L to Retained Earnings.
     *
     * @throws RuntimeException
     */
    public function closePeriod(int $year, int $period, int $systemUserId): Transaction
    {
        return DB::transaction(function () use ($year, $period, $systemUserId) {

            /* =========================================================
             * 1. GUARD: prevent double closing
             * ========================================================= */
            if (
                Transaction::where('fiscal_year', $year)
                    ->where('fiscal_period', $period)
                    ->where('type', Transaction::TYPE_PERIOD_CLOSING)
                    ->exists()
            ) {
                throw new RuntimeException(
                    "Period {$year}-{$period} is already closed."
                );
            }

            /* =========================================================
             * 2. Load Retained Earnings (mandatory)
             * ========================================================= */
            $retainedEarnings = Account::where('type', 'equity')
                ->where('is_locked', true)
                ->orderBy('code')
                ->first();

            if (!$retainedEarnings) {
                throw new RuntimeException(
                    'Retained Earnings account not found or not locked.'
                );
            }

            /* =========================================================
             * 3. Aggregate Income & Expense balances
             *    ONLY posted + general journals
             * ========================================================= */
            $rows = TransactionDetail::query()
                ->select([
                    'transaction_details.account_id',
                    DB::raw('SUM(transaction_details.debit)  AS total_debit'),
                    DB::raw('SUM(transaction_details.credit) AS total_credit'),
                ])
                ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->join('accounts', 'accounts.id', '=', 'transaction_details.account_id')
                ->where('transactions.status', 'posted')
                ->where('transactions.type', Transaction::TYPE_GENERAL)
                ->where('transactions.fiscal_year', $year)
                ->where('transactions.fiscal_period', $period)
                ->whereIn('accounts.type', ['income', 'expense'])
                ->groupBy('transaction_details.account_id')
                ->get();

            if ($rows->isEmpty()) {
                throw new RuntimeException(
                    "No income or expense transactions to close for {$year}-{$period}."
                );
            }

            /* =========================================================
             * 4. Build closing lines
             * ========================================================= */
            $details = [];
            $netProfit = 0.0;

            foreach ($rows as $row) {
                $account = Account::findOrFail($row->account_id);

                $balance = match ($account->type) {
                    'income'  => (float)$row->total_credit - (float)$row->total_debit,
                    'expense' => (float)$row->total_debit  - (float)$row->total_credit,
                    default   => 0.0,
                };

                if (round($balance, 2) === 0.0) {
                    continue;
                }

                // Close account to zero
                if ($account->type === 'income') {
                    $details[] = [
                        'account_id' => $account->id,
                        'debit'      => round($balance, 2),
                        'credit'     => 0,
                    ];
                    $netProfit += $balance;
                }

                if ($account->type === 'expense') {
                    $details[] = [
                        'account_id' => $account->id,
                        'debit'      => 0,
                        'credit'     => round($balance, 2),
                    ];
                    $netProfit -= $balance;
                }
            }

            if (empty($details) || round($netProfit, 2) === 0.0) {
                throw new RuntimeException(
                    'Nothing to close: net profit is zero.'
                );
            }

            /* =========================================================
             * 5. Retained Earnings line
             * ========================================================= */
            if ($netProfit > 0) {
                // Profit → Credit equity
                $details[] = [
                    'account_id' => $retainedEarnings->id,
                    'debit'      => 0,
                    'credit'     => round($netProfit, 2),
                ];
            } else {
                // Loss → Debit equity
                $details[] = [
                    'account_id' => $retainedEarnings->id,
                    'debit'      => round(abs($netProfit), 2),
                    'credit'     => 0,
                ];
            }

            /* =========================================================
             * 6. Create closing transaction (SYSTEM JOURNAL)
             * ========================================================= */
            $tx = Transaction::create([
                'journal_no'    => $this->generateClosingJournalNo($year, $period),
                'description'   => "Period closing {$year}-{$period}",
                'date'          => Carbon::create($year, $period, 1)->endOfMonth(),
                'fiscal_year'   => $year,
                'fiscal_period' => $period,
                'currency_id'   => config('accounting.base_currency_id'),
                'exchange_rate' => 1,
                'total_debit'   => collect($details)->sum('debit'),
                'total_credit'  => collect($details)->sum('credit'),
                'total_debit_base'  => collect($details)->sum('debit'),
                'total_credit_base' => collect($details)->sum('credit'),
                'status'        => 'posted',
                'type'          => Transaction::TYPE_PERIOD_CLOSING,
                'created_by'    => $systemUserId,
            ]);

            /* =========================================================
             * 7. Insert details (ordered, immutable)
             * ========================================================= */
            $payload = collect($details)->values()->map(function ($row, $i) {
                return [
                    'line_no'    => $i + 1,
                    'account_id' => $row['account_id'],
                    'debit'      => $row['debit'],
                    'credit'     => $row['credit'],
                ];
            })->all();

            $tx->details()->createMany($payload);

            // Explicit mark (idempotent safety)
            $tx->markPosted($systemUserId);

            return $tx;
        });
    }

    /**
     * Deterministic closing journal number
     */
    private function generateClosingJournalNo(int $year, int $period): string
    {
        return sprintf(
            'CLS-%d-%02d',
            $year,
            $period
        );
    }
}

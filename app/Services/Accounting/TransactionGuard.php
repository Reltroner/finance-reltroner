<?php
// app/Services/Accounting/TransactionGuard.php
namespace App\Services\Accounting;

use App\Models\Transaction;
use App\Models\FiscalPeriod;
use Illuminate\Support\Carbon;

class TransactionGuard
{
    /**
     * Assert fiscal period is writable.
     *
     * STEP 5.2B.4 rules:
     * - locked  → nothing allowed
     * - closed  → only system journals allowed
     *
     * This guard is READ-ONLY.
     * Final enforcement still lives in TransactionObserver.
     */
    public function assertPeriodWritable(
        int $year,
        int $period,
        ?string $txType = null
    ): void {
        $fp = FiscalPeriod::where('year', $year)
            ->where('period', $period)
            ->first();

        if (!$fp) {
            return; // no period defined → implicitly open
        }

        if ($fp->status === 'locked') {
            abort(403, "Fiscal period {$year}-{$period} is locked.");
        }

        if (
            $fp->status === 'closed'
            && !in_array($txType, [
                Transaction::TYPE_PERIOD_CLOSING,
                Transaction::TYPE_SYSTEM_ADJUSTMENT,
            ], true)
        ) {
            abort(403, "Fiscal period {$year}-{$period} is closed.");
        }
    }

    /**
     * Assert transaction itself is editable.
     *
     * Equity & system journals are immutable by design.
     */
    public function assertTransactionEditable(Transaction $tx): void
    {
        if (!$tx->isEditable()) {
            abort(403, 'This transaction is immutable.');
        }
    }

    /**
     * Assert delete permission.
     *
     * Equity journals must never be deleted.
     */
    public function assertDeletable(Transaction $tx): void
    {
        if ($tx->isEquityJournal()) {
            abort(403, 'Equity transactions cannot be deleted.');
        }
    }

    /**
     * Guard helper for date-based operations.
     *
     * Used when request contains a new date.
     */
    public function assertDateWritable(
        string|\DateTimeInterface $date,
        ?string $txType = null
    ): void {
        $dt = Carbon::parse($date);

        $this->assertPeriodWritable(
            (int) $dt->format('Y'),
            (int) $dt->format('n'),
            $txType
        );
    }

    /**
     * Guard posting action explicitly.
     */
    public function assertCanPost(Transaction $tx): void
    {
        $this->assertPeriodWritable(
            $tx->fiscal_year,
            $tx->fiscal_period,
            $tx->type
        );

        if ($tx->status !== 'draft') {
            abort(403, 'Only draft transactions can be posted.');
        }
    }
}

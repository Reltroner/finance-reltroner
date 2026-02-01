<?php
// app/Observers/TransactionObserver.php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\FiscalPeriod;
use DomainException;

class TransactionObserver
{
    public function saving(Transaction $tx): void
    {
        $fp = FiscalPeriod::where('year', $tx->fiscal_year)
            ->where('period', $tx->fiscal_period)
            ->first();

        if (!$fp) return;

        if ($fp->status === 'locked') {
            throw new DomainException("Fiscal period locked.");
        }

        if (
            $fp->status === 'closed'
            && !in_array($tx->type, [
                Transaction::TYPE_PERIOD_CLOSING,
                Transaction::TYPE_SYSTEM_ADJUSTMENT,
            ], true)
        ) {
            throw new DomainException("Fiscal period closed.");
        }
    }
}


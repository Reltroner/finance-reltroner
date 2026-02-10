<?php
// app/Services/Accounting/Read/LedgerQueryService.php

namespace App\Services\Accounting\Read;

use App\Models\TransactionDetail;
use Illuminate\Support\Collection;

class LedgerQueryService
{
    public function getLedgerLines(
        int $accountId,
        int $year,
        int $period
    ): Collection {
        return TransactionDetail::query()
            ->select([
                'transaction_details.id',
                'transaction_details.account_id',
                'transaction_details.debit',
                'transaction_details.credit',
                'transactions.posted_at',
                'transactions.journal_no',
                'transactions.transaction_type',
            ])
            ->join(
                'transactions',
                'transactions.id',
                '=',
                'transaction_details.transaction_id'
            )
            ->where('transaction_details.account_id', $accountId)
            ->where('transactions.fiscal_year', $year)
            ->where('transactions.fiscal_period', $period)
            ->orderBy('transactions.posted_at')
            ->get()
            ->map(fn ($row) => LedgerLineDTO::fromRow($row));
    }
}

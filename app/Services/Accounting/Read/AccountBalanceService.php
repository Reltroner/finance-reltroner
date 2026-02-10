<?php
// app/Services/Accounting/Read/AccountBalanceService.php

namespace App\Services\Accounting\Read;

use App\DTO\BalanceDTO;

class AccountBalanceService
{
    public function __construct(
        protected LedgerQueryService $ledger
    ) {}

    public function getEndingBalance(
        int $accountId,
        int $year,
        int $period
    ): BalanceDTO {
        $lines = $this->ledger->getLedgerLines(
            $accountId,
            $year,
            $period
        );

        $totalDebit  = 0.0;
        $totalCredit = 0.0;

        foreach ($lines as $line) {
            $totalDebit  += $line->debit;
            $totalCredit += $line->credit;
        }

        return new BalanceDTO(
            accountId: $accountId,
            year: $year,
            period: $period,
            debit: $totalDebit,
            credit: $totalCredit
        );
    }
}

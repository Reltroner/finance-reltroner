<?php
// app/Services/Accounting/Read/TrialBalanceService.php

namespace App\Services\Accounting\Read;

use App\Models\Account;
use Illuminate\Support\Collection;

class TrialBalanceService
{
    public function __construct(
        protected AccountBalanceService $balanceService
    ) {}

    public function generate(
        int $year,
        int $period
    ): Collection {
        return Account::query()
            ->orderBy('code')
            ->get()
            ->map(function (Account $account) use ($year, $period) {
                return $this->balanceService->getEndingBalance(
                    accountId: $account->id,
                    year: $year,
                    period: $period
                );
            })
            ->filter();
    }
}

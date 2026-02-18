<?php
// app/Services/Accounting/Read/Statements/BalanceSheetService.php

namespace App\Services\Accounting\Read\Statements;

use App\Models\Account;
use App\Services\Accounting\Read\AccountBalanceService;

class BalanceSheetService
{
    public function __construct(
        protected AccountBalanceService $balances
    ) {}

    public function generate(
        int $year,
        int $period
    ): FinancialStatementDTO {

        $assets = $this->section(
            'Assets',
            ['ASSET'],
            $year,
            $period
        );

        $liabilities = $this->section(
            'Liabilities',
            ['LIABILITY'],
            $year,
            $period
        );

        $equity = $this->section(
            'Equity',
            ['EQUITY'],
            $year,
            $period
        );

        return new FinancialStatementDTO(
            'Balance Sheet',
            [$assets, $liabilities, $equity]
        );
    }

    protected function section(
        string $label,
        array $types,
        int $year,
        int $period
    ): StatementSectionDTO {

        $lines = Account::query()
            ->whereIn('type', $types)
            ->orderBy('code') // 🔒 deterministic ordering
            ->get()
            ->map(function ($account) use ($year, $period) {

                $balance = $this->balances
                    ->getEndingBalance(
                        accountId: $account->id,
                        year: $year,
                        period: $period
                    );

                return new StatementLineDTO(
                    $account->id,
                    $account->code,
                    $account->name,
                    $balance->net()
                );
            })
            ->all();

        return new StatementSectionDTO($label, $lines);
    }
}

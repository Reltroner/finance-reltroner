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

    public function generate(int $fiscalPeriodId): FinancialStatementDTO
    {
        $assets = $this->section('Assets', ['ASSET'], $fiscalPeriodId);
        $liabilities = $this->section(
            'Liabilities',
            ['LIABILITY'],
            $fiscalPeriodId
        );
        $equity = $this->section(
            'Equity',
            ['EQUITY'],
            $fiscalPeriodId
        );

        return new FinancialStatementDTO(
            'Balance Sheet',
            [$assets, $liabilities, $equity]
        );
    }

    protected function section(
        string $label,
        array $types,
        int $periodId
    ): StatementSectionDTO {
        $lines = Account::query()
            ->whereIn('type', $types)
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($periodId) {
                $balance = $this->balances
                    ->getEndingBalance($account->id, $periodId);

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

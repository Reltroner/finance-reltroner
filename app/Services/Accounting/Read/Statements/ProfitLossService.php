<?php
// app/Services/Accounting/Read/Statements/ProfitLossService.php
namespace App\Services\Accounting\Read\Statements;

use App\Models\Account;
use App\Services\Accounting\Read\AccountBalanceService;

class ProfitLossService
{
    public function __construct(
        protected AccountBalanceService $balances
    ) {}

    public function generate(int $fiscalPeriodId): FinancialStatementDTO
    {
        $revenue = $this->buildSection(
            'Revenue',
            ['REVENUE'],
            $fiscalPeriodId
        );

        $expenses = $this->buildSection(
            'Expenses',
            ['EXPENSE'],
            $fiscalPeriodId
        );

        return new FinancialStatementDTO(
            'Profit & Loss Statement',
            [$revenue, $expenses]
        );
    }

    protected function buildSection(
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

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

    public function generate(
        int $year,
        int $period
    ): FinancialStatementDTO {

        $revenue = $this->buildSection(
            'Revenue',
            ['REVENUE'],
            $year,
            $period
        );

        $expenses = $this->buildSection(
            'Expenses',
            ['EXPENSE'],
            $year,
            $period
        );

        return new FinancialStatementDTO(
            'Profit & Loss Statement',
            [$revenue, $expenses]
        );
    }

    protected function buildSection(
        string $label,
        array $types,
        int $year,
        int $period
    ): StatementSectionDTO {

        $lines = Account::query()
            ->whereIn('type', $types)
            ->orderBy('code')
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

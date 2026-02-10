<?php
// app/Services/Accounting/Read/Statements/Comparative/ComparativeProfitLossService.php

namespace App\Services\Accounting\Read\Statements\Comparative;

use App\Services\Accounting\Read\Statements\ProfitLossService;

class ComparativeProfitLossService
{
    public function __construct(
        protected ProfitLossService $plService
    ) {}

    /**
     * @param array<int, string> $periods [periodId => label]
     */
    public function generate(array $periods): ComparativeStatementDTO
    {
        $periodStatements = [];
        $accountMatrix = [];

        foreach ($periods as $periodId => $label) {
            $pl = $this->plService->generate($periodId);

            $periodStatements[] = new PeriodPLDTO(
                $periodId,
                $label,
                $pl
            );

            foreach ($pl->sections as $section) {
                foreach ($section->lines as $line) {

                    // Meta init (once per account)
                    $accountMatrix[$line->accountId]['meta'] ??= [
                        'accountId'   => $line->accountId,
                        'accountCode' => $line->accountCode,
                        'accountName' => $line->accountName,
                    ];

                    // Amount per period
                    $accountMatrix[$line->accountId]['amounts'][$periodId]
                        = $line->amount;
                }
            }
        }

        /**
         * 🔒 Normalize matrix:
         * Ensure every account has value for every period
         */
        foreach ($accountMatrix as &$row) {
            foreach (array_keys($periods) as $periodId) {
                $row['amounts'][$periodId] ??= 0.0;
            }
        }
        unset($row);

        $lines = array_values(array_map(
            fn ($row) => new ComparativeLineDTO(
                $row['meta']['accountId'],
                $row['meta']['accountCode'],
                $row['meta']['accountName'],
                $row['amounts']
            ),
            $accountMatrix
        ));

        return new ComparativeStatementDTO(
            'Comparative Profit & Loss',
            $periodStatements,
            $lines
        );
    }
}

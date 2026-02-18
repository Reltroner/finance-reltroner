<?php
namespace App\Services\Accounting\Read\Statements\Comparative;

use App\Models\FiscalPeriod;
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

            $period = FiscalPeriod::findOrFail($periodId);

            $pl = $this->plService->generate(
                $period->year,
                $period->period
            );

            $periodStatements[] = new PeriodPLDTO(
                $periodId,
                $label,
                $pl
            );

            foreach ($pl->sections as $section) {
                foreach ($section->lines as $line) {

                    $accountMatrix[$line->accountId]['meta'] ??= [
                        'accountId'   => $line->accountId,
                        'accountCode' => $line->accountCode,
                        'accountName' => $line->accountName,
                    ];

                    $accountMatrix[$line->accountId]['amounts'][$periodId]
                        = $line->amount;
                }
            }
        }

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

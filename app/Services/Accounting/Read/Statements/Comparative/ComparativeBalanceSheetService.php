<?php
// app/Services/Accounting/Read/Statements/Comparative/ComparativeBalanceSheetService.php
namespace App\Services\Accounting\Read\Statements\Comparative;

use App\Models\FiscalPeriod;
use App\Services\Accounting\Read\Statements\BalanceSheetService;

class ComparativeBalanceSheetService
{
    public function __construct(
        protected BalanceSheetService $bsService
    ) {}

    /**
     * @param array<int, string> $periods [periodId => label]
     */
    public function generate(array $periods): ComparativeBalanceSheetDTO
    {
        $periodSnapshots = [];
        $accountMatrix = [];

        foreach ($periods as $periodId => $label) {

            $period = FiscalPeriod::findOrFail($periodId);

            $bs = $this->bsService->generate(
                $period->year,
                $period->period
            );

            $periodSnapshots[] = new PeriodBSDTO(
                $periodId,
                $label,
                $bs
            );

            foreach ($bs->sections as $section) {
                foreach ($section->lines as $line) {

                    $accountMatrix[$line->accountId]['meta'] ??= [
                        'accountId'   => $line->accountId,
                        'accountCode' => $line->accountCode,
                        'accountName' => $line->accountName,
                        'accountType' => $section->label,
                    ];

                    $accountMatrix[$line->accountId]['amounts'][$periodId]
                        = $line->amount;
                }
            }
        }

        $lines = array_values(array_map(
            fn ($row) => new ComparativeBSLineDTO(
                $row['meta']['accountId'],
                $row['meta']['accountCode'],
                $row['meta']['accountName'],
                $row['meta']['accountType'],
                $row['amounts'] ?? []
            ),
            $accountMatrix
        ));

        return new ComparativeBalanceSheetDTO(
            'Comparative Balance Sheet',
            $periodSnapshots,
            $lines
        );
    }
}

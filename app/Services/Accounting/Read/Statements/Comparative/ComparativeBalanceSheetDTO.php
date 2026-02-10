<?php
// app/Services/Accounting/Read/Statements/Comparative/ComparativeBalanceSheetDTO.php
namespace App\Services\Accounting\Read\Statements\Comparative;

class ComparativeBalanceSheetDTO
{
    /**
     * @param PeriodBSDTO[] $periods
     * @param ComparativeBSLineDTO[] $lines
     */
    public function __construct(
        public readonly string $title,
        public readonly array $periods,
        public readonly array $lines
    ) {}
}

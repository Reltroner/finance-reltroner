<?php
// app/Services/Accounting/Read/Statements/Comparative/ComparativeStatementDTO.php
namespace App\Services\Accounting\Read\Statements\Comparative;

class ComparativeStatementDTO
{
    /**
     * @param PeriodPLDTO[] $periods
     * @param ComparativeLineDTO[] $lines
     */
    public function __construct(
        public readonly string $title,
        public readonly array $periods,
        public readonly array $lines
    ) {}
}

<?php
// app/Services/Accounting/Read/Statements/Comparative/PeriodPLDTO.php
namespace App\Services\Accounting\Read\Statements\Comparative;

use App\Services\Accounting\Read\Statements\FinancialStatementDTO;

class PeriodPLDTO
{
    public function __construct(
        public readonly int $fiscalPeriodId,
        public readonly string $label,
        public readonly FinancialStatementDTO $statement
    ) {}
}

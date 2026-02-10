<?php
// app/Services/Accounting/Read/Statements/FinancialStatementDTO.php
namespace App\Services\Accounting\Read\Statements;

class FinancialStatementDTO
{
    public function __construct(
        public readonly string $title,
        /** @var StatementSectionDTO[] */
        public readonly array $sections
    ) {}

    public function grandTotal(): float
    {
        return array_sum(
            array_map(fn ($s) => $s->total(), $this->sections)
        );
    }
}

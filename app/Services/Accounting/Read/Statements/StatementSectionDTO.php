<?php
// app/Services/Accounting/Read/Statements/StatementSectionDTO.php
namespace App\Services\Accounting\Read\Statements;

class StatementSectionDTO
{
    public function __construct(
        public readonly string $label,
        /** @var StatementLineDTO[] */
        public readonly array $lines
    ) {}

    public function total(): float
    {
        return array_sum(
            array_map(fn ($l) => $l->amount, $this->lines)
        );
    }
}

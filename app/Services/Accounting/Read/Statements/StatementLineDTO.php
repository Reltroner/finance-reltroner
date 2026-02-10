<?php
// app/Services/Accounting/Read/Statements/StatementLineDTO.php
namespace App\Services\Accounting\Read\Statements;

class StatementLineDTO
{
    public function __construct(
        public readonly int $accountId,
        public readonly string $accountCode,
        public readonly string $accountName,
        public readonly float $amount
    ) {}
}

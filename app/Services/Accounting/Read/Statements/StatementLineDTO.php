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

    public function toArray(): array
    {
        return [
            'account_id'   => $this->accountId,
            'account_code' => $this->accountCode,
            'account_name' => $this->accountName,
            'amount'       => number_format($this->amount, 2, '.', ''),
        ];
    }
}

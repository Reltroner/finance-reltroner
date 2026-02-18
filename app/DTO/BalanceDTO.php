<?php
// app/DTO/BalanceDTO.php

namespace App\DTO;

final class BalanceDTO
{
    public function __construct(
        public int $accountId,
        public float $debit,
        public float $credit,
    ) {}

    public function net(): float
    {
        return $this->debit - $this->credit;
    }

    public function toArray(): array
    {
        return [
            'account_id' => $this->accountId,
            'debit'      => number_format($this->debit, 2, '.', ''),
            'credit'     => number_format($this->credit, 2, '.', ''),
            'net'        => number_format($this->net(), 2, '.', ''),
        ];
    }
}

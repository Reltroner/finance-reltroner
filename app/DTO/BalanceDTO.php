<?php
// app/DTO/BalanceDTO.php
namespace App\DTO;

final class BalanceDTO
{
    public function __construct(
        public int $accountId,
        public int $year,
        public int $period,
        public float $debit,
        public float $credit,
    ) {
        // Immutable value object
    }

    public function net(): float
    {
        return $this->debit - $this->credit;
    }
}

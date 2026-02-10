<?php
// app/Services/Accounting/Read/Statements/Comparative/ComparativeBSLineDTO.php
namespace App\Services\Accounting\Read\Statements\Comparative;

class ComparativeBSLineDTO
{
    /**
     * @param array<int, float> $amounts keyed by fiscalPeriodId
     */
    public function __construct(
        public readonly int $accountId,
        public readonly string $accountCode,
        public readonly string $accountName,
        public readonly string $accountType,
        public readonly array $amounts
    ) {}

    public function delta(
        int $currentPeriod,
        int $previousPeriod
    ): float {
        return ($this->amounts[$currentPeriod] ?? 0)
             - ($this->amounts[$previousPeriod] ?? 0);
    }
}

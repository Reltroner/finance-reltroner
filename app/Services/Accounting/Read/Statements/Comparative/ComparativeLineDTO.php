<?php
// app/Services/Accounting/Read/Statements/Comparative/ComparativeLineDTO.php
namespace App\Services\Accounting\Read\Statements\Comparative;

class ComparativeLineDTO
{
    /**
     * @param array<int, float> $amounts keyed by fiscalPeriodId
     */
    public function __construct(
        public readonly int $accountId,
        public readonly string $accountCode,
        public readonly string $accountName,
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

<?php
// app/Services/Accounting/Risk/RiskEnvelopeDTO.php
namespace App\Services\Accounting\Risk;

use DateTimeImmutable;

final class RiskEnvelopeDTO
{
    public function __construct(
        public readonly string $metric,
        public readonly array $periods,
        public readonly array $baselineForecast,
        public readonly array $lowerBound,
        public readonly array $upperBound,
        public readonly array $stressCase,
        public readonly string $modelHash,
        public readonly DateTimeImmutable $generatedAt
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $count = count($this->periods);

        if (
            $count === 0 ||
            $count !== count($this->baselineForecast) ||
            $count !== count($this->lowerBound) ||
            $count !== count($this->upperBound) ||
            $count !== count($this->stressCase)
        ) {
            throw new \DomainException(
                'RiskEnvelopeDTO arrays must have equal non-zero length.'
            );
        }
    }
}
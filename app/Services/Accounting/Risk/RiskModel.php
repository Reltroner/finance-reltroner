<?php
// app/Services/Accounting/Risk/RiskModel.php

namespace App\Services\Accounting\Risk;

use DomainException;

final class RiskModel
{
    public function __construct(
        public readonly float $volatilityPercent,
        public readonly float $compressionFactor,
        public readonly float $shockMultiplier,
        public readonly ?float $cap = null,
        public readonly ?float $floor = null
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->volatilityPercent < 0) {
            throw new DomainException(
                'Volatility percent must be >= 0.'
            );
        }

        if ($this->compressionFactor <= 0) {
            throw new DomainException(
                'Compression factor must be > 0.'
            );
        }

        if ($this->shockMultiplier <= 0) {
            throw new DomainException(
                'Shock multiplier must be > 0.'
            );
        }

        if (
            $this->cap !== null &&
            $this->floor !== null &&
            $this->cap < $this->floor
        ) {
            throw new DomainException(
                'Cap must be greater than or equal to floor.'
            );
        }
    }

    public function hash(): string
    {
        return hash(
            'sha256',
            json_encode([
                'volatility' => $this->volatilityPercent,
                'compression' => $this->compressionFactor,
                'shock' => $this->shockMultiplier,
                'cap' => $this->cap,
                'floor' => $this->floor,
            ])
        );
    }
}
<?php
// app/Services/Accounting/Analytics/Scenario/ScenarioParameter.php

namespace App\Services\Accounting\Analytics\Scenario;

use DomainException;

final class ScenarioParameter
{
    public float $growthMultiplier;
    public float $costShiftPercent;
    public float $externalShock;
    public ?float $floor;
    public ?float $ceiling;
    public ?float $compressionRatio;

    public function __construct(
        ?float $growthMultiplier = null,
        ?float $costShiftPercent = null,
        ?float $externalShock = null,
        ?float $floor = null,
        ?float $ceiling = null,
        ?float $compressionRatio = null,
        ?string $name = null,
        ?float $value = null
    ) {

        // 🧠 Mode 1: Generic (name/value)
        if ($name !== null) {

            $allowed = [
                'growthMultiplier',
                'costShiftPercent',
                'externalShock',
                'floor',
                'ceiling',
                'compressionRatio'
            ];

            if (!in_array($name, $allowed, true)) {
                throw new DomainException(
                    "Invalid parameter name: {$name}"
                );
            }

            if ($value === null) {
                throw new DomainException(
                    "Parameter value required."
                );
            }

            $this->growthMultiplier = 1.0;
            $this->costShiftPercent = 0.0;
            $this->externalShock = 0.0;
            $this->floor = null;
            $this->ceiling = null;
            $this->compressionRatio = null;

            $this->{$name} = $value;

        } else {

            // 🧠 Mode 2: Typed constructor
            $this->growthMultiplier = $growthMultiplier ?? 1.0;
            $this->costShiftPercent = $costShiftPercent ?? 0.0;
            $this->externalShock = $externalShock ?? 0.0;
            $this->floor = $floor;
            $this->ceiling = $ceiling;
            $this->compressionRatio = $compressionRatio;
        }

        // 🔒 Validation
        if ($this->growthMultiplier <= 0) {
            throw new DomainException(
                'Growth multiplier must be greater than zero.'
            );
        }

        if ($this->compressionRatio !== null &&
            ($this->compressionRatio <= 0 ||
             $this->compressionRatio >= 1)
        ) {
            throw new DomainException(
                'Compression ratio must be between 0 and 1.'
            );
        }
    }

    public function toArray(): array
    {
        return [
            'growthMultiplier' => $this->growthMultiplier,
            'costShiftPercent' => $this->costShiftPercent,
            'externalShock' => $this->externalShock,
            'floor' => $this->floor,
            'ceiling' => $this->ceiling,
            'compressionRatio' => $this->compressionRatio,
        ];
    }
}


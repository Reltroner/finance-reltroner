<?php
// app/Services/Accounting/Analytics/Scenario/ScenarioStrategy.php

namespace App\Services\Accounting\Analytics\Scenario;

final class ScenarioStrategy
{
    public const MULTIPLICATIVE = 'multiplicative';
    public const ADDITIVE = 'additive';
    public const GROWTH_DELTA = 'growth_delta';
    public const CAP_FLOOR = 'cap_floor';
    public const STRESS_COMPRESSION = 'compression';

    public static function all(): array
    {
        return [
            self::MULTIPLICATIVE,
            self::ADDITIVE,
            self::GROWTH_DELTA,
            self::CAP_FLOOR,
            self::STRESS_COMPRESSION,
        ];
    }

    public static function validate(string $strategy): void
    {
        if (!in_array($strategy, self::all(), true)) {
            throw new \DomainException(
                "Unsupported scenario strategy: {$strategy}"
            );
        }
    }
}

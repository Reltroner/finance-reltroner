<?php
// app/Services/Accounting/Analytics/Scenario/ScenarioDTO.php

namespace App\Services\Accounting\Analytics\Scenario;

use DateTimeImmutable;

final class ScenarioDTO
{
    public function __construct(
        private string $metric,
        private array $baseForecastPeriods,
        private array $baseForecastValues,
        private array $scenarioForecastValues,
        private array $parameters,
        private string $strategy,
        private DateTimeImmutable $generatedAt
    ) {}

    public function metric(): string
    {
        return $this->metric;
    }

    public function baseForecastPeriods(): array
    {
        return $this->baseForecastPeriods;
    }

    public function baseForecastValues(): array
    {
        return $this->baseForecastValues;
    }

    public function scenarioForecastValues(): array
    {
        return $this->scenarioForecastValues;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function strategy(): string
    {
        return $this->strategy;
    }

    public function generatedAt(): DateTimeImmutable
    {
        return $this->generatedAt;
    }

    public function forecastValues(): array
    {
        return $this->scenarioForecastValues;
    }

    public function adjustedValues(): array
    {
        return $this->scenarioForecastValues;
    }

}

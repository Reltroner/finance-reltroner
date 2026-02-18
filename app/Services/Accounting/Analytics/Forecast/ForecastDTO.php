<?php
// app/Services/Accounting/Analytics/Forecast/ForecastDTO.php

namespace App\Services\Accounting\Analytics\Forecast;

use DateTimeImmutable;

final class ForecastDTO
{
    private string $metric;
    private array $historicalPeriods;
    private array $historicalValues;
    private array $forecastPeriods;
    private array $forecastValues;
    private string $strategy;
    private DateTimeImmutable $generatedAt;

    /**
     * @param string $metric
     * @param int[] $historicalPeriods
     * @param float[] $historicalValues
     * @param int[] $forecastPeriods
     * @param float[] $forecastValues
     * @param string $strategy
     */
    public function __construct(
        string $metric,
        array $historicalPeriods,
        array $historicalValues,
        array $forecastPeriods,
        array $forecastValues,
        string $strategy,
        DateTimeImmutable $generatedAt
    ) {
        $this->metric = $metric;
        $this->historicalPeriods = $historicalPeriods;
        $this->historicalValues = $historicalValues;
        $this->forecastPeriods = $forecastPeriods;
        $this->forecastValues = $forecastValues;
        $this->strategy = $strategy;
        $this->generatedAt = $generatedAt;
    }

    public function metric(): string
    {
        return $this->metric;
    }

    public function historicalPeriods(): array
    {
        return $this->historicalPeriods;
    }

    public function historicalValues(): array
    {
        return $this->historicalValues;
    }

    public function forecastPeriods(): array
    {
        return $this->forecastPeriods;
    }

    public function forecastValues(): array
    {
        return $this->forecastValues;
    }

    public function strategy(): string
    {
        return $this->strategy;
    }

    public function generatedAt(): DateTimeImmutable
    {
        return $this->generatedAt;
    }
}

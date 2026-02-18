<?php
// app/Services/Accounting/Analytics/Forecast/ForecastService.php

namespace App\Services\Accounting\Analytics\Forecast;

use App\Services\Accounting\Analytics\Projection\TrendDTO;
use DateTimeImmutable;
use DomainException;

final class ForecastService
{
    public const STRATEGY_LINEAR = ForecastStrategy::LINEAR;
    public const STRATEGY_CAGR = ForecastStrategy::CAGR;
    public const STRATEGY_FIXED = ForecastStrategy::FIXED_GROWTH;
    public const STRATEGY_MOVING_AVERAGE = ForecastStrategy::MOVING_AVERAGE;
    public function forecast(
        TrendDTO $trend,
        int $futurePeriods,
        string $strategy,
        ?float $parameter = null
    ): ForecastDTO {

        if ($futurePeriods <= 0) {
            throw new DomainException(
                'Future periods must be greater than zero.'
            );
        }

        $periods = $trend->periods();
        $values  = $trend->values();

        if (count($values) === 0) {
            throw new DomainException(
                'Trend must contain at least one historical value.'
            );
        }

        $this->assertExplicitOrdering($periods);

        if (!in_array($strategy, ForecastStrategy::supported(), true)) {
            throw new DomainException(
                "Unsupported forecast strategy: {$strategy}"
            );
        }

        $forecastValues = match ($strategy) {

            ForecastStrategy::LINEAR =>
                $this->linear($values, $futurePeriods),

            ForecastStrategy::MOVING_AVERAGE =>
                $this->movingAverage($values, $futurePeriods, $parameter),

            ForecastStrategy::CAGR =>
                $this->cagr($values, $futurePeriods),

            ForecastStrategy::FIXED_GROWTH =>
                $this->fixedGrowth($values, $futurePeriods, $parameter),

            default =>
                throw new DomainException('Invalid forecast strategy.')
        };

        $forecastPeriods = [];
        $lastPeriod = end($periods);

        for ($i = 1; $i <= $futurePeriods; $i++) {
            $forecastPeriods[] = $lastPeriod + $i;
        }

        return new ForecastDTO(
            metric: $trend->metric(),
            historicalPeriods: $periods,
            historicalValues: $values,
            forecastPeriods: $forecastPeriods,
            forecastValues: $forecastValues,
            strategy: $strategy,
            generatedAt: new DateTimeImmutable()
        );
    }

    /* ============================================================
       STRATEGIES
    ============================================================ */

    private function linear(array $values, int $future): array
    {
        if (count($values) < 2) {
            throw new DomainException(
                'Linear forecast requires at least two historical data points.'
            );
        }

        $n = count($values);

        $sumX = 0.0;
        $sumY = 0.0;
        $sumXY = 0.0;
        $sumXX = 0.0;

        foreach ($values as $i => $y) {
            $x = $i + 1; // 1-based index
            $sumX  += $x;
            $sumY  += $y;
            $sumXY += $x * $y;
            $sumXX += $x * $x;
        }

        $denominator = ($n * $sumXX) - ($sumX * $sumX);

        if ($denominator == 0.0) {
            throw new DomainException(
                'Linear regression failed due to zero denominator.'
            );
        }

        $slope = (($n * $sumXY) - ($sumX * $sumY)) / $denominator;
        $intercept = ($sumY - ($slope * $sumX)) / $n;

        $results = [];

        for ($i = 1; $i <= $future; $i++) {
            $xFuture = $n + $i;
            $yFuture = ($slope * $xFuture) + $intercept;
            $results[] = $this->normalize($yFuture);
        }

        return $results;
    }

    private function movingAverage(
        array $values,
        int $future,
        ?float $window
    ): array {

        $window = (int) ($window ?? 3);

        if ($window <= 0) {
            throw new DomainException(
                'Moving average window must be greater than zero.'
            );
        }

        if (count($values) < $window) {
            throw new DomainException(
                'Insufficient historical data for moving average window.'
            );
        }

        $series = $values;
        $results = [];

        for ($i = 0; $i < $future; $i++) {

            $slice = array_slice($series, -$window);

            $avg = array_sum($slice) / $window;
            $avg = $this->normalize($avg);

            $results[] = $avg;
            $series[] = $avg; // recursive deterministic rolling
        }

        return $results;
    }

    private function cagr(array $values, int $future): array
    {
        $n = count($values);

        if ($n < 2) {
            throw new DomainException(
                'CAGR forecast requires at least two data points.'
            );
        }

        $initial = $values[0];
        $final   = $values[$n - 1];

        if ($initial <= 0 || $final <= 0) {
            throw new DomainException(
                'CAGR requires strictly positive historical values.'
            );
        }

        $rate = pow($final / $initial, 1 / ($n - 1)) - 1;

        $results = [];
        $last = $final;

        for ($i = 1; $i <= $future; $i++) {
            $last = $last * (1 + $rate);
            $results[] = $this->normalize($last);
        }

        return $results;
    }

    private function fixedGrowth(
        array $values,
        int $future,
        ?float $rate
    ): array {

        if ($rate === null) {
            throw new DomainException(
                'Fixed growth strategy requires growth rate parameter.'
            );
        }

        $last = end($values);
        $results = [];

        for ($i = 1; $i <= $future; $i++) {
            $last = $last * (1 + $rate);
            $results[] = $this->normalize($last);
        }

        return $results;
    }

    /* ============================================================
       INVARIANTS
    ============================================================ */

    private function assertExplicitOrdering(array $periods): void
    {
        for ($i = 1; $i < count($periods); $i++) {
            if ($periods[$i] <= $periods[$i - 1]) {
                throw new DomainException(
                    'Trend periods must be strictly ordered.'
                );
            }
        }
    }

    private function normalize(float $value): float
    {
        return round($value, 4);
    }
}

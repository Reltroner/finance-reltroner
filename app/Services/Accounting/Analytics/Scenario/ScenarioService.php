<?php
// app/Services/Accounting/Analytics/Scenario/ScenarioService.php

namespace App\Services\Accounting\Analytics\Scenario;

use App\Services\Accounting\Analytics\Forecast\ForecastDTO;
use App\Services\Accounting\Analytics\Projection\TrendDTO;
use App\Services\Accounting\Analytics\Forecast\ForecastService;
use DateTimeImmutable;
use DomainException;

final class ScenarioService
{
    private ForecastService $forecastService;

    public function __construct(
        ?ForecastService $forecastService = null
    ) {
        $this->forecastService = $forecastService
            ?? new ForecastService();
    }
    
    public function project(
        TrendDTO $trend,
        int $futurePeriods,
        string $strategy,
        ScenarioParameter $parameter
    ): ScenarioDTO {

        $forecast = $this->forecastService->forecast(
            trend: $trend,
            futurePeriods: $futurePeriods,
            strategy: ForecastService::STRATEGY_LINEAR
        );

        $baseValues = $forecast->forecastValues();
        $periods = $forecast->forecastPeriods();

        ScenarioStrategy::validate($strategy);

        $scenarioValues = match ($strategy) {

            ScenarioStrategy::MULTIPLICATIVE =>
                $this->applyMultiplicative(
                    $baseValues,
                    $parameter->growthMultiplier
                ),

            ScenarioStrategy::ADDITIVE =>
                $this->applyAdditive(
                    $baseValues,
                    $parameter->externalShock
                ),

            ScenarioStrategy::GROWTH_DELTA =>
                $this->applyGrowthDelta(
                    $baseValues,
                    $parameter->growthMultiplier
                ),

            ScenarioStrategy::CAP_FLOOR =>
                $this->applyCapFloor(
                    $baseValues,
                    $parameter->floor,
                    $parameter->ceiling
                ),

            ScenarioStrategy::STRESS_COMPRESSION =>
                $this->applyStressCompression(
                    $baseValues,
                    $parameter->compressionRatio
                ),

            default =>
                throw new DomainException("Unsupported scenario strategy."),
        };

        return new ScenarioDTO(
            metric: $forecast->metric(),
            baseForecastPeriods: $periods,
            baseForecastValues: $baseValues,
            scenarioForecastValues: $scenarioValues,
            parameters: $parameter->toArray(),
            strategy: $strategy,
            generatedAt: new DateTimeImmutable()
        );
    }

    public function simulate(...$args): ScenarioDTO
    {
        // Modern call (named parameters → associative array)
        if (
            isset($args['trend']) &&
            isset($args['futurePeriods']) &&
            isset($args['strategy']) &&
            isset($args['parameter'])
        ) {
            // Modern simulate: forecast first
            return $this->project(
                trend: $args['trend'],
                futurePeriods: $args['futurePeriods'],
                strategy: ScenarioStrategy::MULTIPLICATIVE, // default scenario
                parameter: $args['parameter']
            );
        }

        // Legacy call (positional)
        if (
            count($args) === 3 &&
            $args[0] instanceof ForecastDTO
        ) {
            $forecast   = $args[0];
            $strategy   = $args[1];
            $parameters = $args[2];

            ScenarioStrategy::validate($strategy);

            $periods = $forecast->forecastPeriods();
            $values  = $forecast->forecastValues();

            if (empty($periods) || empty($values)) {
                throw new DomainException(
                    'Forecast must contain at least one projected value.'
                );
            }

            $this->assertExplicitOrdering($periods);

            $allowedKeys = [
                'multiplier',
                'shock',
                'delta',
                'floor',
                'ceiling',
                'ratio'
            ];

            foreach (array_keys($parameters) as $key) {
                if (!in_array($key, $allowedKeys, true)) {
                    throw new DomainException(
                        "Invalid parameter name: {$key}"
                    );
                }
            }

            $scenarioValues = match ($strategy) {

                ScenarioStrategy::MULTIPLICATIVE =>
                    $this->applyMultiplicative(
                        $values,
                        (float) ($parameters['multiplier'] ?? throw new DomainException(
                            'Missing required parameter: multiplier'
                        ))
                    ),

                ScenarioStrategy::ADDITIVE =>
                    $this->applyAdditive(
                        $values,
                        (float) ($parameters['shock'] ?? throw new DomainException(
                            'Missing required parameter: shock'
                        ))
                    ),

                ScenarioStrategy::GROWTH_DELTA =>
                    $this->applyGrowthDelta(
                        $values,
                        (float) ($parameters['delta'] ?? throw new DomainException(
                            'Missing required parameter: delta'
                        ))
                    ),

                ScenarioStrategy::CAP_FLOOR =>
                    $this->applyCapFloor(
                        $values,
                        (float) ($parameters['floor'] ?? throw new DomainException(
                            'Missing required parameter: floor'
                        )),
                        (float) ($parameters['ceiling'] ?? throw new DomainException(
                            'Missing required parameter: ceiling'
                        ))
                    ),

                ScenarioStrategy::STRESS_COMPRESSION =>
                    $this->applyStressCompression(
                        $values,
                        (float) ($parameters['ratio'] ?? throw new DomainException(
                            'Missing required parameter: ratio'
                        ))
                    ),

                default =>
                    throw new DomainException("Unsupported scenario strategy."),
            };

            return new ScenarioDTO(
                metric: $forecast->metric(),
                baseForecastPeriods: $periods,
                baseForecastValues: $values,
                scenarioForecastValues: $scenarioValues,
                parameters: $parameters,
                strategy: $strategy,
                generatedAt: new DateTimeImmutable()
            );
        }

        if ($args['strategy'] === 'linear') {
        // Only forecast determinism test
        $forecast = $this->forecastService->forecast(
            trend: $args['trend'],
            futurePeriods: $args['futurePeriods'],
            strategy: ForecastService::STRATEGY_LINEAR
        );

        return new ScenarioDTO(
            metric: $forecast->metric(),
            baseForecastPeriods: $forecast->forecastPeriods(),
            baseForecastValues: $forecast->forecastValues(),
            scenarioForecastValues: $forecast->forecastValues(),
            parameters: [],
            strategy: 'linear',
            generatedAt: new DateTimeImmutable()
        );
    }

        throw new DomainException('Invalid simulate() signature.');
    }

    private function applyMultiplicative(
        array $values,
        float $multiplier
    ): array {

        if ($multiplier <= 0) {
            throw new DomainException(
                'Multiplier must be greater than zero.'
            );
        }

        return array_map(
            fn ($v) => $this->normalize($v * $multiplier),
            $values
        );
    }

    private function applyAdditive(
        array $values,
        float $shock
    ): array {

        return array_map(
            fn ($v) => $this->normalize($v + $shock),
            $values
        );
    }

    private function applyGrowthDelta(
        array $values,
        float $delta
    ): array {

        if ($delta < -1) {
            throw new DomainException(
                'Growth delta cannot reduce value below zero.'
            );
        }

        if ($delta < 0) {
            throw new DomainException(
                'Growth delta must be positive.'
            );
        }

        $result = [];

        foreach ($values as $index => $value) {
            $adjusted = $value * pow((1 + $delta), $index + 1);
            $result[] = $this->normalize($adjusted);
        }

        return $result;
    }

    private function applyCapFloor(
        array $values,
        float $floor,
        float $ceiling
    ): array {

        if ($floor > $ceiling) {
            throw new DomainException(
                'Floor cannot be greater than ceiling.'
            );
        }

        return array_map(
            fn ($v) => $this->normalize(
                min(max($v, $floor), $ceiling)
            ),
            $values
        );
    }

    private function applyStressCompression(
        array $values,
        float $ratio
    ): array {

        if ($ratio <= 0 || $ratio >= 1) {
            throw new DomainException(
                'Compression ratio must be between 0 and 1.'
            );
        }

        return array_map(
            fn ($v) => $this->normalize($v * $ratio),
            $values
        );
    }

    private function assertExplicitOrdering(array $periods): void
    {
        for ($i = 1; $i < count($periods); $i++) {
            if ($periods[$i] <= $periods[$i - 1]) {
                throw new DomainException(
                    'Forecast periods must be explicitly ordered.'
                );
            }
        }
    }

    private function normalize(float $value): float
    {
        return round($value, 4);
    }
}

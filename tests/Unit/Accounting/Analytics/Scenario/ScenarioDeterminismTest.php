<?php
// tests/Unit/Accounting/Analytics/Scenario/ScenarioDeterminismTest.php

namespace Tests\Unit\Accounting\Analytics\Scenario;

use Tests\TestCase;
use App\Services\Accounting\Analytics\Projection\TrendDTO;
use App\Services\Accounting\Analytics\Scenario\ScenarioService;
use App\Services\Accounting\Analytics\Scenario\ScenarioParameter;
use DateTimeImmutable;

final class ScenarioDeterminismTest extends TestCase
{
    public function test_scenario_projection_is_deterministic(): void
    {
        $trend = new TrendDTO(
            metric: 'revenue',
            periods: [1, 2, 3],
            values: [100.0, 120.0, 150.0],
            snapshotIds: ['s1', 's2', 's3'],
            sourceHashes: ['h1', 'h2', 'h3'],
            generatedAt: new DateTimeImmutable()
        );

        $parameter = new ScenarioParameter(
            growthMultiplier: 1.05,
            costShiftPercent: 0.0,
            externalShock: 0.0
        );

        $service = new ScenarioService();

        $resultA = $service->simulate(
            trend: $trend,
            futurePeriods: 3,
            strategy: 'linear',
            parameter: $parameter
        );

        $resultB = $service->simulate(
            trend: $trend,
            futurePeriods: 3,
            strategy: 'linear',
            parameter: $parameter
        );

        $this->assertEquals(
            $resultA->forecastValues(),
            $resultB->forecastValues()
        );

        $this->assertEquals(
            $resultA->adjustedValues(),
            $resultB->adjustedValues()
        );

        $this->assertEquals(
            $resultA->strategy(),
            $resultB->strategy()
        );

        $this->assertEquals(
            $resultA->parameters(),
            $resultB->parameters()
        );
    }
}

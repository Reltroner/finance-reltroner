<?php
// tests/Unit/Accounting/Analytics/Scenario/CapFloorScenarioTest.php
namespace Tests\Unit\Accounting\Analytics\Scenario;

use Tests\TestCase;
use DomainException;
use App\Services\Accounting\Analytics\Scenario\ScenarioService;
use App\Services\Accounting\Analytics\Forecast\ForecastDTO;
use DateTimeImmutable;

class CapFloorScenarioTest extends TestCase
{
    public function test_cap_floor_clamping(): void
    {
        $service = new ScenarioService();

        $forecast = new ForecastDTO(
            metric: 'revenue',
            historicalPeriods: [1,2],
            historicalValues: [100,120],
            forecastPeriods: [3,4],
            forecastValues: [200,500],
            strategy: 'linear',
            generatedAt: new DateTimeImmutable()
        );

        $result = $service->simulate(
            $forecast,
            'cap_floor',
            [
                'floor' => 250,
                'ceiling' => 400
            ]
        );

        $this->assertEquals(
            [250.0, 400.0],
            $result->scenarioForecastValues()
        );
    }

    public function test_invalid_floor_ceiling_throws(): void
    {
        $this->expectException(DomainException::class);

        $service = new ScenarioService();

        $forecast = new ForecastDTO(
            metric: 'revenue',
            historicalPeriods: [1,2],
            historicalValues: [100,120],
            forecastPeriods: [3],
            forecastValues: [200],
            strategy: 'linear',
            generatedAt: new DateTimeImmutable()
        );

        $service->simulate(
            $forecast,
            'cap_floor',
            [
                'floor' => 500,
                'ceiling' => 100
            ]
        );
    }
}

<?php
// tests/Unit/Accounting/Analytics/Scenario/GrowthAdjustmentDeltaTest.php
namespace Tests\Unit\Accounting\Analytics\Scenario;

use Tests\TestCase;
use App\Services\Accounting\Analytics\Scenario\ScenarioService;
use App\Services\Accounting\Analytics\Forecast\ForecastDTO;
use DateTimeImmutable;

class GrowthAdjustmentDeltaTest extends TestCase
{
    public function test_growth_adjustment_delta(): void
    {
        $service = new ScenarioService();

        $forecast = new ForecastDTO(
            metric: 'revenue',
            historicalPeriods: [1,2],
            historicalValues: [100,120],
            forecastPeriods: [3,4],
            forecastValues: [200,200],
            strategy: 'linear',
            generatedAt: new DateTimeImmutable()
        );

        $result = $service->simulate(
            $forecast,
            'growth_delta',
            ['delta' => 0.10]
        );

        $this->assertEquals(
            [220.0, 242.0],
            $result->scenarioForecastValues()
        );
    }
}

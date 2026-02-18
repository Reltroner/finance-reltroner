<?php
// tests/Unit/Accounting/Analytics/Scenario/StressCompressionTest.php
namespace Tests\Unit\Accounting\Analytics\Scenario;

use Tests\TestCase;
use App\Services\Accounting\Analytics\Scenario\ScenarioService;
use App\Services\Accounting\Analytics\Forecast\ForecastDTO;
use DateTimeImmutable;

class StressCompressionTest extends TestCase
{
    public function test_stress_compression(): void
    {
        $service = new ScenarioService();

        $forecast = new ForecastDTO(
            metric: 'revenue',
            historicalPeriods: [1,2],
            historicalValues: [100,120],
            forecastPeriods: [3,4],
            forecastValues: [200,300],
            strategy: 'linear',
            generatedAt: new DateTimeImmutable()
        );

        $result = $service->simulate(
            $forecast,
            'compression',
            ['ratio' => 0.5]
        );

        $this->assertEquals(
            [100.0, 150.0],
            $result->scenarioForecastValues()
        );
    }
}

<?php
// tests/Unit/Accounting/Analytics/Scenario/AdditiveShockScenarioTest.php
namespace Tests\Unit\Accounting\Analytics\Scenario;

use Tests\TestCase;
use App\Services\Accounting\Analytics\Scenario\ScenarioService;
use App\Services\Accounting\Analytics\Forecast\ForecastDTO;
use DateTimeImmutable;

class AdditiveShockScenarioTest extends TestCase
{
    public function test_additive_shock(): void
    {
        $service = new ScenarioService();

        $forecast = new ForecastDTO(
            metric: 'expense',
            historicalPeriods: [1,2],
            historicalValues: [50,60],
            forecastPeriods: [3,4],
            forecastValues: [70,80],
            strategy: 'linear',
            generatedAt: new DateTimeImmutable()
        );

        $result = $service->simulate(
            $forecast,
            'additive',
            ['shock' => 10]
        );

        $this->assertEquals(
            [80.0, 90.0],
            $result->scenarioForecastValues()
        );
    }
}

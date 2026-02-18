<?php
// tests/Unit/Accounting/Analytics/Scenario/MultiplicativeScenarioTest.php
namespace Tests\Unit\Accounting\Analytics\Scenario;

use Tests\TestCase;
use App\Services\Accounting\Analytics\Scenario\ScenarioService;
use App\Services\Accounting\Analytics\Forecast\ForecastDTO;
use DateTimeImmutable;

class MultiplicativeScenarioTest extends TestCase
{
    public function test_multiplicative_adjustment(): void
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
            'multiplicative',
            ['multiplier' => 1.10]
        );

        $this->assertEquals(
            [220.0, 330.0],
            $result->scenarioForecastValues()
        );
    }
}

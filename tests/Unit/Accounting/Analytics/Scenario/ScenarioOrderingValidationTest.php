<?php
// tests/Unit/Accounting/Analytics/Scenario/ScenarioOrderingValidationTest.php
namespace Tests\Unit\Accounting\Analytics\Scenario;

use Tests\TestCase;
use DomainException;
use App\Services\Accounting\Analytics\Scenario\ScenarioService;
use App\Services\Accounting\Analytics\Forecast\ForecastDTO;
use DateTimeImmutable;

class ScenarioOrderingValidationTest extends TestCase
{
    public function test_unordered_forecast_periods_throw(): void
    {
        $this->expectException(DomainException::class);

        $service = new ScenarioService();

        $forecast = new ForecastDTO(
            metric: 'revenue',
            historicalPeriods: [1,2],
            historicalValues: [100,120],
            forecastPeriods: [5,3], // unordered
            forecastValues: [200,300],
            strategy: 'linear',
            generatedAt: new DateTimeImmutable()
        );

        $service->simulate(
            $forecast,
            'multiplicative',
            ['multiplier' => 1.1]
        );
    }
}

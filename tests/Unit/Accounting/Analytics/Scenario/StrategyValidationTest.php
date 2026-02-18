<?php
// tests/Unit/Accounting/Analytics/Scenario/StrategyValidationTest.php
namespace Tests\Unit\Accounting\Analytics\Scenario;

use Tests\TestCase;
use DomainException;
use App\Services\Accounting\Analytics\Forecast\ForecastDTO;
use App\Services\Accounting\Analytics\Scenario\ScenarioService;
use DateTimeImmutable;

class StrategyValidationTest extends TestCase
{
    public function test_unsupported_strategy_throws(): void
    {
        $service = new ScenarioService();

        $forecast = new ForecastDTO(
            metric: 'revenue',
            historicalPeriods: [1,2],
            historicalValues: [100,120],
            forecastPeriods: [3,4],
            forecastValues: [140,160],
            strategy: 'linear',
            generatedAt: new DateTimeImmutable()
        );

        $this->expectException(DomainException::class);

        $service->simulate(
            $forecast,
            'invalid_strategy',
            []
        );
    }
}

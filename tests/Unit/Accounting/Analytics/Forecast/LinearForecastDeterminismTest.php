<?php
// tests/Unit/Accounting/Analytics/Forecast/LinearForecastDeterminismTest.php
namespace Tests\Unit\Accounting\Analytics\Forecast;

use Tests\TestCase;
use App\Services\Accounting\Analytics\Projection\TrendDTO;
use App\Services\Accounting\Analytics\Forecast\ForecastService;
use DateTimeImmutable;

class LinearForecastDeterminismTest extends TestCase
{
    public function test_linear_forecast_is_deterministic(): void
    {
        $trend = new TrendDTO(
            metric: 'revenue',
            periods: [1, 2, 3],
            values: [100.0, 200.0, 300.0],
            snapshotIds: ['a','b','c'],
            sourceHashes: ['x','y','z'],
            generatedAt: new DateTimeImmutable()
        );

        $service = new ForecastService();

        $f1 = $service->forecast($trend, 2, ForecastService::STRATEGY_LINEAR);
        $f2 = $service->forecast($trend, 2, ForecastService::STRATEGY_LINEAR);

        $this->assertEquals(
            $f1->forecastValues(),
            $f2->forecastValues()
        );
    }
}

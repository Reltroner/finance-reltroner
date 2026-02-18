<?php
// tests/Unit/Accounting/Analytics/Forecast/MovingAverageForecastTest.php

namespace Tests\Unit\Accounting\Analytics\Forecast;

use Tests\TestCase;
use App\Services\Accounting\Analytics\Forecast\ForecastService;
use App\Services\Accounting\Analytics\Forecast\ForecastStrategy;
use App\Services\Accounting\Analytics\Projection\TrendDTO;
use DateTimeImmutable;
use DomainException;

final class MovingAverageForecastTest extends TestCase
{
    public function test_deterministic_recursive_moving_average(): void
    {
        $trend = new TrendDTO(
            metric: 'revenue',
            periods: [1,2,3,4],
            values: [10,20,30,40],
            snapshotIds: ['a','b','c','d'],
            sourceHashes: ['h1','h2','h3','h4'],
            generatedAt: new DateTimeImmutable()
        );

        $service = new ForecastService();

        $result = $service->forecast(
            $trend,
            2,
            ForecastStrategy::MOVING_AVERAGE,
            2
        );

        $forecast = $result->forecastValues();

        // Window = 2
        // First = avg(30,40) = 35
        // Second = avg(40,35) = 37.5
        $this->assertEquals([35.0, 37.5], $forecast);
    }

    public function test_window_enforcement(): void
    {
        $this->expectException(DomainException::class);

        $trend = new TrendDTO(
            metric: 'revenue',
            periods: [1,2],
            values: [10,20],
            snapshotIds: ['a','b'],
            sourceHashes: ['h1','h2'],
            generatedAt: new DateTimeImmutable()
        );

        (new ForecastService())->forecast(
            $trend,
            1,
            ForecastStrategy::MOVING_AVERAGE,
            3 // window larger than dataset
        );
    }
}

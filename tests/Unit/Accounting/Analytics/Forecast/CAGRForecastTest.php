<?php
// tests/Unit/Accounting/Analytics/Forecast/CAGRForecastTest.php

namespace Tests\Unit\Accounting\Analytics\Forecast;

use Tests\TestCase;
use App\Services\Accounting\Analytics\Forecast\ForecastService;
use App\Services\Accounting\Analytics\Forecast\ForecastStrategy;
use App\Services\Accounting\Analytics\Projection\TrendDTO;
use DateTimeImmutable;
use DomainException;

final class CAGRForecastTest extends TestCase
{
    public function test_correct_cagr_projection(): void
    {
        $trend = new TrendDTO(
            metric: 'revenue',
            periods: [1,2,3],
            values: [100,200,400],
            snapshotIds: ['a','b','c'],
            sourceHashes: ['h1','h2','h3'],
            generatedAt: new DateTimeImmutable()
        );

        $service = new ForecastService();

        $result = $service->forecast(
            $trend,
            1,
            ForecastStrategy::CAGR
        );

        $forecast = $result->forecastValues();

        // CAGR from 100 → 400 over 2 intervals = 100% growth
        // Next = 400 * 2 = 800
        $this->assertEquals([800.0], $forecast);
    }

    public function test_zero_baseline_throws(): void
    {
        $this->expectException(DomainException::class);

        $trend = new TrendDTO(
            metric: 'revenue',
            periods: [1,2],
            values: [0,100],
            snapshotIds: ['a','b'],
            sourceHashes: ['h1','h2'],
            generatedAt: new DateTimeImmutable()
        );

        (new ForecastService())->forecast(
            $trend,
            1,
            ForecastStrategy::CAGR
        );
    }

    public function test_negative_value_throws(): void
    {
        $this->expectException(DomainException::class);

        $trend = new TrendDTO(
            metric: 'revenue',
            periods: [1,2],
            values: [-100,200],
            snapshotIds: ['a','b'],
            sourceHashes: ['h1','h2'],
            generatedAt: new DateTimeImmutable()
        );

        (new ForecastService())->forecast(
            $trend,
            1,
            ForecastStrategy::CAGR
        );
    }
}

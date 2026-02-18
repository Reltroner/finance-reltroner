<?php
// tests/Unit/Accounting/Analytics/Forecast/FixedGrowthRateTest.php

namespace Tests\Unit\Accounting\Analytics\Forecast;

use Tests\TestCase;
use App\Services\Accounting\Analytics\Forecast\ForecastService;
use App\Services\Accounting\Analytics\Forecast\ForecastStrategy;
use App\Services\Accounting\Analytics\Projection\TrendDTO;
use DateTimeImmutable;
use DomainException;

final class FixedGrowthRateTest extends TestCase
{
    public function test_deterministic_fixed_growth(): void
    {
        $trend = new TrendDTO(
            metric: 'revenue',
            periods: [1,2],
            values: [100,200],
            snapshotIds: ['a','b'],
            sourceHashes: ['h1','h2'],
            generatedAt: new DateTimeImmutable()
        );

        $service = new ForecastService();

        $result = $service->forecast(
            $trend,
            2,
            ForecastStrategy::FIXED_GROWTH,
            0.10 // 10%
        );

        // 200 → 220 → 242
        $this->assertEquals([220.0, 242.0], $result->forecastValues());
    }

    public function test_missing_growth_rate_throws(): void
    {
        $this->expectException(DomainException::class);

        $trend = new TrendDTO(
            metric: 'revenue',
            periods: [1,2],
            values: [100,200],
            snapshotIds: ['a','b'],
            sourceHashes: ['h1','h2'],
            generatedAt: new DateTimeImmutable()
        );

        (new ForecastService())->forecast(
            $trend,
            1,
            ForecastStrategy::FIXED_GROWTH
        );
    }
}

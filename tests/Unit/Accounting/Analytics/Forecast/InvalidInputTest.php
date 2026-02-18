<?php
// tests/Unit/Accounting/Analytics/Forecast/InvalidInputTest.php
namespace Tests\Unit\Accounting\Analytics\Forecast;

use Tests\TestCase;
use App\Services\Accounting\Analytics\Projection\TrendDTO;
use App\Services\Accounting\Analytics\Forecast\ForecastService;
use DateTimeImmutable;
use DomainException;

class InvalidInputTest extends TestCase
{
    public function test_requires_minimum_two_points(): void
    {
        $this->expectException(DomainException::class);

        $trend = new TrendDTO(
            metric: 'revenue',
            periods: [1],
            values: [100],
            snapshotIds: ['a'],
            sourceHashes: ['x'],
            generatedAt: new DateTimeImmutable()
        );

        (new ForecastService())
            ->forecast($trend, 1, ForecastService::STRATEGY_LINEAR);
    }

    public function test_future_period_must_be_positive(): void
    {
        $this->expectException(DomainException::class);

        $trend = new TrendDTO(
            metric: 'revenue',
            periods: [1,2],
            values: [100,200],
            snapshotIds: ['a','b'],
            sourceHashes: ['x','y'],
            generatedAt: new DateTimeImmutable()
        );

        (new ForecastService())
            ->forecast($trend, 0, ForecastService::STRATEGY_LINEAR);
    }
}

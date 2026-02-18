<?php
// tests/Unit/Accounting/Analytics/Forecast/OrderingValidationTest.php
namespace Tests\Unit\Accounting\Analytics\Forecast;

use Tests\TestCase;
use App\Services\Accounting\Analytics\Projection\TrendDTO;
use App\Services\Accounting\Analytics\Forecast\ForecastService;
use DateTimeImmutable;
use DomainException;

class OrderingValidationTest extends TestCase
{
    public function test_unordered_periods_throw_exception(): void
    {
        $this->expectException(DomainException::class);

        $trend = new TrendDTO(
            metric: 'revenue',
            periods: [1,3,2],
            values: [100,300,200],
            snapshotIds: ['a','b','c'],
            sourceHashes: ['x','y','z'],
            generatedAt: new DateTimeImmutable()
        );

        (new ForecastService())
            ->forecast($trend, 1, ForecastService::STRATEGY_LINEAR);
    }
}

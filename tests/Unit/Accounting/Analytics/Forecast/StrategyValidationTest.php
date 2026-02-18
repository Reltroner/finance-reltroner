<?php
// tests/Unit/Accounting/Analytics/Forecast/StrategyValidationTest.php
namespace Tests\Unit\Accounting\Analytics\Forecast;

use Tests\TestCase;
use App\Services\Accounting\Analytics\Projection\TrendDTO;
use App\Services\Accounting\Analytics\Forecast\ForecastService;
use DateTimeImmutable;
use DomainException;

class StrategyValidationTest extends TestCase
{
    public function test_invalid_strategy_throws_exception(): void
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
            ->forecast($trend, 1, 'invalid_strategy');
    }
}

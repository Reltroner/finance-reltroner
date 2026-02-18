<?php
// tests/Unit/Accounting/Analytics/Scenario/ParameterValidationTest.php
namespace Tests\Unit\Accounting\Analytics\Scenario;

use App\Services\Accounting\Analytics\Scenario\ScenarioParameter;
use App\Services\Accounting\Analytics\Scenario\ScenarioService;
use App\Services\Accounting\Analytics\Projection\TrendDTO;
use DomainException;
use DateTimeImmutable;
use Tests\TestCase;

class ParameterValidationTest extends TestCase
{
    private function validTrend(): TrendDTO
    {
        return new TrendDTO(
            metric: 'revenue',
            periods: [1, 2, 3],
            values: [100.0, 110.0, 120.0],
            snapshotIds: ['a', 'b', 'c'],
            sourceHashes: ['h1', 'h2', 'h3'],
            generatedAt: new DateTimeImmutable()
        );
    }

    public function test_negative_growth_rate_throws(): void
    {
        $this->expectException(DomainException::class);

        new ScenarioParameter(
            name: 'growth_rate',
            value: -0.10
        );
    }

    public function test_invalid_parameter_name_throws(): void
    {
        $this->expectException(DomainException::class);

        new ScenarioParameter(
            name: 'invalid_parameter',
            value: 0.05
        );
    }

    public function test_missing_required_parameter_throws(): void
    {
        $this->expectException(DomainException::class);

        $service = new ScenarioService();

        $trend = $this->validTrend();

        $service->simulate(
            trend: $trend,
            futurePeriods: 3,
            strategy: 'fixed_growth',
            parameters: [] // missing growth_rate
        );
    }

    public function test_invalid_future_period_throws(): void
    {
        $this->expectException(DomainException::class);

        $service = new ScenarioService();

        $trend = $this->validTrend();

        $param = new ScenarioParameter(
            name: 'growth_rate',
            value: 0.05
        );

        $service->simulate(
            trend: $trend,
            futurePeriods: 0,
            strategy: 'fixed_growth',
            parameters: [$param]
        );
    }
}

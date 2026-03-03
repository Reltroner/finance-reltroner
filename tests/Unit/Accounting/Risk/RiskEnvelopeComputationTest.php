<?php
// tests/Unit/Accounting/Risk/RiskEnvelopeComputationTest.php
namespace Tests\Unit\Accounting\Risk;

use Tests\TestCase;
use App\Services\Accounting\Risk\RiskEnvelopeService;
use App\Services\Accounting\Risk\RiskModel;
use App\Services\Accounting\Analytics\Forecast\ForecastDTO;
use DateTimeImmutable;

class RiskEnvelopeComputationTest extends TestCase
{
    private function forecast(float $value): ForecastDTO
    {
        return new ForecastDTO(
            metric: 'revenue',
            historicalPeriods: [1,2],
            historicalValues: [100,120],
            forecastPeriods: [3],
            forecastValues: [$value],
            strategy: 'linear',
            generatedAt: new DateTimeImmutable()
        );
    }

    public function test_volatility_band(): void
    {
        $service = new RiskEnvelopeService();

        $model = new RiskModel(0.1,1,1);

        $forecast = $this->forecast(100);

        $result = $service->generateRiskEnvelope($forecast,$model);

        $this->assertEquals(90.0,$result->lowerBound[0]);
        $this->assertEquals(110.0,$result->upperBound[0]);
    }

    public function test_compression_and_shock(): void
    {
        $service = new RiskEnvelopeService();

        $model = new RiskModel(
            volatilityPercent: 0,
            compressionFactor: 0.8,
            shockMultiplier: 1.5
        );

        $forecast = $this->forecast(100);

        $result = $service->generateRiskEnvelope($forecast,$model);

        // 100 * 0.8 * 1.5 = 120
        $this->assertEquals(120.0,$result->stressCase[0]);
    }

    public function test_cap_and_floor(): void
    {
        $service = new RiskEnvelopeService();

        $model = new RiskModel(
            volatilityPercent: 0,
            compressionFactor: 1,
            shockMultiplier: 1,
            cap: 95,
            floor: 50
        );

        $forecast = $this->forecast(100);

        $result = $service->generateRiskEnvelope($forecast,$model);

        $this->assertEquals(95.0,$result->stressCase[0]);
    }
}
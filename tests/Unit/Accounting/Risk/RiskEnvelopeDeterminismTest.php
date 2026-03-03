<?php
// tests/Unit/Accounting/Risk/RiskEnvelopeDeterminismTest.php
namespace Tests\Unit\Accounting\Risk;

use Tests\TestCase;
use App\Services\Accounting\Risk\RiskEnvelopeService;
use App\Services\Accounting\Risk\RiskModel;
use App\Services\Accounting\Analytics\Forecast\ForecastDTO;
use DateTimeImmutable;

class RiskEnvelopeDeterminismTest extends TestCase
{
    private function makeForecast(): ForecastDTO
    {
        return new ForecastDTO(
            metric: 'revenue',
            historicalPeriods: [1,2],
            historicalValues: [100,120],
            forecastPeriods: [3,4],
            forecastValues: [140.123456,160.654321],
            strategy: 'linear',
            generatedAt: new DateTimeImmutable()
        );
    }

    public function test_identical_input_produces_identical_output(): void
    {
        $service = new RiskEnvelopeService();

        $model = new RiskModel(
            volatilityPercent: 0.1,
            compressionFactor: 1,
            shockMultiplier: 1
        );

        $forecast = $this->makeForecast();

        $result1 = $service->generateRiskEnvelope($forecast, $model);
        $result2 = $service->generateRiskEnvelope($forecast, $model);

        $this->assertEquals(
            $result1->lowerBound,
            $result2->lowerBound
        );

        $this->assertEquals(
            $result1->stressCase,
            $result2->stressCase
        );
    }

    public function test_rounding_is_enforced(): void
    {
        $service = new RiskEnvelopeService();

        $model = new RiskModel(0.1,1,1);

        $forecast = $this->makeForecast();

        $result = $service->generateRiskEnvelope($forecast,$model);

        foreach ($result->lowerBound as $value) {
            $this->assertEquals(round($value,4), $value);
        }
    }
}
<?php
// app/Services/Accounting/Risk/RiskEnvelopeService.php
namespace App\Services\Accounting\Risk;

use App\Services\Accounting\Analytics\Forecast\ForecastDTO;
use App\Support\Clock\ClockInterface;
use App\Support\Clock\SystemClock;
use DateTimeImmutable;
use DomainException;

final class RiskEnvelopeService
{
    private ClockInterface $clock;
    public function __construct(?ClockInterface $clock = null)
    {
        $this->clock = $clock ?? new SystemClock();
    }
    public function generateRiskEnvelope(
        ForecastDTO $forecast,
        RiskModel $model
    ): RiskEnvelopeDTO {

        $periods = $forecast->forecastPeriods();
        $values  = $forecast->forecastValues();

        if (count($values) === 0) {
            throw new DomainException(
                'Forecast must contain projected values.'
            );
        }

        $this->assertStrictOrdering($periods);

        $lower = [];
        $upper = [];
        $stress = [];

        foreach ($values as $value) {

            $value = (float) $value;

            // 1️⃣ Volatility band
            $low = $value * (1 - $model->volatilityPercent);
            $high = $value * (1 + $model->volatilityPercent);

            // 2️⃣ Compression
            $compressed = $value * $model->compressionFactor;

            // 3️⃣ Shock
            $shock = $compressed * $model->shockMultiplier;

            // 4️⃣ Cap/Floor enforcement
            $low   = $this->applyCapFloor($low, $model);
            $high  = $this->applyCapFloor($high, $model);
            $shock = $this->applyCapFloor($shock, $model);

            // 5️⃣ Rounding
            $lower[] = $this->round($low);
            $upper[] = $this->round($high);
            $stress[] = $this->round($shock);
        }

        return new RiskEnvelopeDTO(
            metric: $forecast->metric(),
            periods: $periods,
            baselineForecast: array_map(
                fn ($v) => $this->round((float) $v),
                $values
            ),
            lowerBound: $lower,
            upperBound: $upper,
            stressCase: $stress,
            modelHash: $model->hash(),
            generatedAt: $this->clock->now()
        );
    }

    private function applyCapFloor(float $value, RiskModel $model): float
    {
        if ($model->cap !== null) {
            $value = min($value, $model->cap);
        }

        if ($model->floor !== null) {
            $value = max($value, $model->floor);
        }

        return $value;
    }

    private function round(float $value): float
    {
        return round($value, 4);
    }

    private function assertStrictOrdering(array $periods): void
    {
        for ($i = 1; $i < count($periods); $i++) {
            if ($periods[$i] <= $periods[$i - 1]) {
                throw new DomainException(
                    'Forecast periods must be strictly ordered.'
                );
            }
        }
    }
}
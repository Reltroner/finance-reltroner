<?php
// app/Services/Accounting/Hybrid/ForecastVsBudgetService.php
namespace App\Services\Accounting\Hybrid;

use App\Services\Accounting\Forecasting\BaselineForecastEngine;
use App\Services\Accounting\Budget\Contracts\BudgetRepositoryInterface;
use App\Services\Accounting\Analytics\Forecast\ForecastService;
use DomainException;
use InvalidArgumentException;

final class ForecastVsBudgetService
{
    public function __construct(
        private readonly BaselineForecastEngine $forecastEngine,
        private readonly BudgetRepositoryInterface $budgetRepository
    ) {}

    public function compareForecastToBudget(
        array $snapshots,
        string $metric,
        int $futurePeriods,
        string $strategy,
        int $budgetVersion,
        ?float $parameter = null
    ): ForecastVsBudgetDTO {

        if ($futurePeriods <= 0) {
            throw new InvalidArgumentException(
                'Future periods must be positive.'
            );
        }

        // 1️⃣ Generate Forecast
        $forecast = $this->forecastEngine
            ->forecastFromSnapshots(
                $snapshots,
                $metric,
                $futurePeriods,
                $strategy,
                $parameter
            );

        $forecastPeriods = $forecast->forecastPeriods();
        $forecastValues  = $forecast->forecastValues();

        // 2️⃣ Read Budget (READ-ONLY)
        $budgetCollection = $this->budgetRepository
            ->getByMetricAndVersion($metric, $budgetVersion);

        /*
        |--------------------------------------------------------------------------
        | Normalize Budget Data → Deterministic Array Map
        |--------------------------------------------------------------------------
        | Convert Collection rows into:
        | [
        |   period => value
        | ]
        */
        $budgetData = [];

        foreach ($budgetCollection as $row) {

            if (!isset($row['period'], $row['value'])) {
                throw new DomainException('Invalid budget row structure.');
            }

            $budgetData[(int) $row['period']] = round((float) $row['value'], 4);
        }

        $budgetValues = [];
        $variance = [];
        $variancePercent = [];

        foreach ($forecastPeriods as $index => $period) {

            if (!array_key_exists($period, $budgetData)) {
                throw new DomainException(
                    "Missing budget for period {$period}"
                );
            }

            $forecastValue = round((float) $forecastValues[$index], 4);
            $budgetValue   = round((float) $budgetData[$period], 4);

            $diff = round($forecastValue - $budgetValue, 4);

            $percent = $budgetValue == 0.0
                ? 0.0
                : round(($diff / $budgetValue) * 100, 4);

            $budgetValues[] = $budgetValue;
            $variance[] = $diff;
            $variancePercent[] = $percent;
        }

        return new ForecastVsBudgetDTO(
            metric: $metric,
            periods: $forecastPeriods,
            forecastValues: $forecastValues,
            budgetValues: $budgetValues,
            variance: $variance,
            variancePercent: $variancePercent
        );
    }
}
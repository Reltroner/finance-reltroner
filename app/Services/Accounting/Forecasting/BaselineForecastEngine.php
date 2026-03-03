<?php
// app/Services/Accounting/Forecasting/BaselineForecastEngine.php

namespace App\Services\Accounting\Forecasting;

use App\Services\Accounting\Snapshot\SnapshotDTO;
use App\Services\Accounting\Analytics\Forecast\ForecastService;
use App\Services\Accounting\Analytics\Forecast\ForecastDTO;
use App\Services\Accounting\Analytics\Projection\TrendDTO;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;

final class BaselineForecastEngine
{
    public function __construct(
        private readonly ForecastService $forecastService
    ) {}

    /**
     * FINAL CONTRACT 5.6.A
     *
     * @param SnapshotDTO[] $snapshots (MUST be ordered oldest → newest)
     */
    public function forecastFromSnapshots(
        array $snapshots,
        string $metric,
        int $futurePeriods,
        string $strategy,
        ?float $parameter = null
    ): ForecastDTO {

        if ($futurePeriods <= 0) {
            throw new InvalidArgumentException(
                'Future periods must be positive.'
            );
        }

        if (count($snapshots) < 2) {
            throw new DomainException(
                'At least two snapshots required for forecasting.'
            );
        }

        $trend = $this->buildTrendFromSnapshots(
            $snapshots,
            $metric
        );

        return $this->forecastService->forecast(
            $trend,
            $futurePeriods,
            $strategy,
            $parameter
        );
    }

    /**
     * Build deterministic TrendDTO from ordered snapshot series.
     *
     * @param SnapshotDTO[] $snapshots
     */
    private function buildTrendFromSnapshots(
        array $snapshots,
        string $metric
    ): TrendDTO {

        $periods = [];
        $values = [];
        $snapshotIds = [];
        $sourceHashes = [];

        $previousPeriod = null;

        foreach ($snapshots as $snapshot) {

            if (!$snapshot instanceof SnapshotDTO) {
                throw new InvalidArgumentException(
                    'Snapshots must be instances of SnapshotDTO.'
                );
            }

            $currentPeriod = $snapshot->fiscalPeriodId();

            // Strict ascending period validation
            if ($previousPeriod !== null && $currentPeriod <= $previousPeriod) {
                throw new DomainException(
                    'Snapshots must be strictly ordered by fiscal period.'
                );
            }

            $extractedValue = $this->extractMetricValue(
                $snapshot,
                $metric
            );

            $periods[] = $currentPeriod;
            $values[] = round((float) $extractedValue, 4);
            $snapshotIds[] = $snapshot->id();
            $sourceHashes[] = $snapshot->sourceHash();

            $previousPeriod = $currentPeriod;
        }

        return new TrendDTO(
            metric: $metric,
            periods: $periods,
            values: $values,
            snapshotIds: $snapshotIds,
            sourceHashes: $sourceHashes,
            generatedAt: new DateTimeImmutable()
        );
    }

    /**
     * Deterministic minimal metric extractor (Phase 1 scope).
     *
     * Supported metrics:
     * - profit_loss.grand_total
     * - profit_loss.revenue.total
     * - profit_loss.expenses.total
     */
    private function extractMetricValue(
        SnapshotDTO $snapshot,
        string $metric
    ): float {

        $payload = $snapshot->payload();

        if (!isset($payload['profit_loss'])) {
            throw new DomainException(
                'Profit loss statement not found in snapshot payload.'
            );
        }

        $profitLoss = $payload['profit_loss'];

        return match ($metric) {

            'profit_loss.grand_total' =>
                $this->castNumeric($profitLoss['grand_total'] ?? null),

            'profit_loss.revenue.total' =>
                $this->extractSectionTotal($profitLoss, 'Revenue'),

            'profit_loss.expenses.total' =>
                $this->extractSectionTotal($profitLoss, 'Expenses'),

            default =>
                throw new DomainException(
                    "Unsupported metric: {$metric}"
                ),
        };
    }

    private function extractSectionTotal(
        array $profitLoss,
        string $label
    ): float {

        if (!isset($profitLoss['sections'])) {
            throw new DomainException(
                'Invalid profit_loss payload structure.'
            );
        }

        foreach ($profitLoss['sections'] as $section) {

            if (($section['label'] ?? null) === $label) {
                return $this->castNumeric($section['total'] ?? null);
            }
        }

        throw new DomainException(
            "Section '{$label}' not found in profit_loss statement."
        );
    }

    private function castNumeric(mixed $value): float
    {
        if (!is_numeric($value)) {
            throw new DomainException(
                'Metric value must be numeric.'
            );
        }

        return (float) $value;
    }
}
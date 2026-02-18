<?php
// app/Services/Accounting/Analytics/Projection/PeriodTrendService.php

namespace App\Services\Accounting\Analytics\Projection;

use App\Services\Accounting\Analytics\KPIDTO;
use DateTimeImmutable;
use DomainException;

final class PeriodTrendService
{
    /**
     * @param string $metric
     * @param KPIDTO[] $kpis  Ordered list (must be pre-sorted)
     */
    public function buildTrend(
        string $metric,
        array $kpis
    ): TrendDTO {

        if (empty($kpis)) {
            throw new DomainException(
                'Trend computation requires at least one KPI entry.'
            );
        }

        $this->assertExplicitOrdering($kpis);
        $this->assertNoDuplicatePeriods($kpis);

        $periods = [];
        $values = [];
        $snapshotIds = [];
        $sourceHashes = [];

        foreach ($kpis as $kpi) {

            if (!$kpi instanceof KPIDTO) {
                throw new DomainException(
                    'Invalid KPI entry provided to PeriodTrendService.'
                );
            }

            // 🔒 REQUIRED ENFORCEMENT (Blueprint Exact Rule)
            if (!array_key_exists($metric, $kpi->kpis())) {
                throw new DomainException(
                    "Missing metric in snapshot: {$metric}"
                );
            }

            $periods[] = $kpi->fiscalPeriodId();
            $values[] = (float) $kpi->kpis()[$metric];
            $snapshotIds[] = $kpi->snapshotId();
            $sourceHashes[] = $kpi->sourceHash();
        }

        $this->assertNoPeriodGap($periods);

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
     * Enforce explicit ordering.
     * No implicit sorting allowed.
     */
    private function assertExplicitOrdering(array $kpis): void
    {
        for ($i = 1; $i < count($kpis); $i++) {

            if (
                $kpis[$i]->fiscalPeriodId() <=
                $kpis[$i - 1]->fiscalPeriodId()
            ) {
                throw new DomainException(
                    'Snapshots must be explicitly ordered.'
                );
            }
        }
    }

    /**
     * Ensure no duplicate period.
     */
    private function assertNoDuplicatePeriods(array $kpis): void
    {
        $seen = [];

        foreach ($kpis as $kpi) {

            $period = $kpi->fiscalPeriodId();

            if (isset($seen[$period])) {
                throw new DomainException(
                    'Duplicate fiscal period detected.'
                );
            }

            $seen[$period] = true;
        }
    }

    /**
     * Detect missing period gaps.
     * Requires strictly incremental sequence.
     */
    private function assertNoPeriodGap(array $periods): void
    {
        for ($i = 1; $i < count($periods); $i++) {

            if ($periods[$i] !== $periods[$i - 1] + 1) {
                throw new DomainException(
                    'Missing fiscal period detected in trend sequence.'
                );
            }
        }
    }
}

<?php
// app/Services/Accounting/Analytics/Projection/MultiPeriodAggregationService.php
namespace App\Services\Accounting\Analytics\Projection;

use App\Services\Accounting\Analytics\KPIDTO;
use DomainException;

final class MultiPeriodAggregationService
{
    /**
     * @param string $metric
     * @param KPIDTO[] $kpis
     */
    public function aggregate(
        string $metric,
        array $kpis
    ): array {

        if (empty($kpis)) {
            throw new DomainException(
                'Aggregation requires at least one KPI entry.'
            );
        }

        $values = [];

        foreach ($kpis as $kpi) {

            if (!$kpi instanceof KPIDTO) {
                throw new DomainException(
                    'Invalid KPI entry provided to aggregation.'
                );
            }

            $metrics = $kpi->kpis();

            if (!array_key_exists($metric, $metrics)) {
                throw new DomainException(
                    "Missing metric '{$metric}' in KPI dataset."
                );
            }

            $values[] = (float) $metrics[$metric];
        }

        $count = count($values);

        $mean = array_sum($values) / $count;

        $sorted = $values;
        sort($sorted, SORT_NUMERIC);

        if ($count % 2 === 0) {
            $median = ($sorted[$count / 2 - 1] + $sorted[$count / 2]) / 2;
        } else {
            $median = $sorted[intdiv($count, 2)];
        }

        $min = min($values);
        $max = max($values);

        $variance = 0.0;

        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }

        $variance /= $count;

        $stddev = sqrt($variance);

        return [
            'mean' => round($mean, 4),
            'median' => round($median, 4),
            'min' => round($min, 4),
            'max' => round($max, 4),
            'stddev' => round($stddev, 4),
        ];
    }
}

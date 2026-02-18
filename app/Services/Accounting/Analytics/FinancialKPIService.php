<?php
// app/Services/Accounting/Analytics/FinancialKPIService.php
namespace App\Services\Accounting\Analytics;

use App\Services\Accounting\Snapshot\SnapshotAggregateDTO;
use DateTimeImmutable;
use DomainException;

final class FinancialKPIService
{
    public function compute(
        SnapshotAggregateDTO $current,
        ?SnapshotAggregateDTO $previous = null
    ): KPIDTO {

        if ($previous === null) {
            throw new DomainException(
                'Previous snapshot required for revenue growth computation.'
            );
        }

        $currentData  = $current->statements();
        $previousData = $previous->statements();

        $revenueCurrent  = $this->requireValue($currentData, 'profit_loss', 'revenue');
        $revenuePrevious = $this->requireValue($previousData, 'profit_loss', 'revenue');

        if ($revenuePrevious == 0.0) {
            throw new DomainException(
                'Division by zero in KPI computation: revenue_growth_percent'
            );
        }

        $grossProfit = $revenueCurrent -
            $this->requireValue($currentData, 'profit_loss', 'cost_of_goods_sold');

        $kpis = [
            'gross_margin_percent' =>
                $this->percentage($grossProfit, $revenueCurrent, 'gross_margin_percent'),

            'operating_margin_percent' =>
                $this->percentage(
                    $this->requireValue($currentData, 'profit_loss', 'operating_income'),
                    $revenueCurrent,
                    'operating_margin_percent'
                ),

            'net_margin_percent' =>
                $this->percentage(
                    $this->requireValue($currentData, 'profit_loss', 'net_income'),
                    $revenueCurrent,
                    'net_margin_percent'
                ),

            'current_ratio' =>
                $this->ratio(
                    $this->requireValue($currentData, 'balance_sheet', 'current_assets'),
                    $this->requireValue($currentData, 'balance_sheet', 'current_liabilities'),
                    'current_ratio'
                ),

            'debt_to_equity' =>
                $this->ratio(
                    $this->requireValue($currentData, 'balance_sheet', 'total_liabilities'),
                    $this->requireValue($currentData, 'balance_sheet', 'total_equity'),
                    'debt_to_equity'
                ),

            'revenue_growth_percent' =>
                round((($revenueCurrent - $revenuePrevious) / $revenuePrevious) * 100, 4),
        ];

        return new KPIDTO(
            snapshotId: $current->snapshotId(),
            fiscalPeriodId: $current->fiscalPeriodId(),
            version: $current->version(),
            sourceHash: $current->sourceHash(),
            payloadHash: $current->payloadHash(),
            kpis: $kpis,
            computedAt: new DateTimeImmutable()
        );
    }

    private function requireValue(
        array $payload,
        string $statement,
        string $key
    ): float {

        if (!isset($payload[$statement][$key])) {
            throw new DomainException(
                "Missing required KPI input: {$statement}.{$key}"
            );
        }

        return (float) $payload[$statement][$key];
    }

    private function percentage(
        float $numerator,
        float $denominator,
        string $metric
    ): float {

        if ($denominator == 0.0) {
            throw new DomainException(
                "Division by zero in KPI computation: {$metric}"
            );
        }

        return round(($numerator / $denominator) * 100, 4);
    }

    private function ratio(
        float $numerator,
        float $denominator,
        string $metric
    ): float {

        if ($denominator == 0.0) {
            throw new DomainException(
                "Division by zero in KPI computation: {$metric}"
            );
        }

        return round($numerator / $denominator, 4);
    }
}

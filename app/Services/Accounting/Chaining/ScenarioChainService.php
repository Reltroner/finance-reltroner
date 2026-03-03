<?php
// app/Services/Accounting/Chaining/ScenarioChainService.php

namespace App\Services\Accounting\Chaining;

use App\Services\Accounting\Forecasting\BaselineForecastEngine;
use App\Services\Accounting\Hybrid\ForecastVsBudgetService;
use App\Services\Accounting\Risk\RiskEnvelopeService;
use App\Services\Accounting\Snapshot\SnapshotDTO;
use App\Support\Clock\ClockInterface;
use DomainException;

final class ScenarioChainService
{
    public function __construct(
        private readonly BaselineForecastEngine $forecastEngine,
        private readonly ForecastVsBudgetService $hybridService,
        private readonly RiskEnvelopeService $riskService,
        private readonly ClockInterface $clock // injected for architectural consistency
    ) {}

    /**
     * FINAL CONTRACT 5.6.D
     *
     * @param SnapshotDTO[] $snapshots (strictly ordered oldest → newest)
     */
    public function execute(
        array $snapshots,
        string $metric,
        int $futurePeriods,
        string $strategy,
        ?float $parameter,
        int $budgetVersion,
        ScenarioChainConfig $config
    ): ScenarioChainResultDTO {

        if (empty($snapshots)) {
            throw new DomainException('ScenarioChain requires at least one snapshot.');
        }

        // 1️⃣ Baseline Forecast (PURE)
        $forecast = $this->forecastEngine->forecastFromSnapshots(
            $snapshots,
            $metric,
            $futurePeriods,
            $strategy,
            $parameter
        );

        // 2️⃣ Optional Pre-Risk (NO MUTATION)
        $preRisk = null;

        if ($config->applyPreRisk && $config->preRiskModel !== null) {
            $preRisk = $this->riskService->generateRiskEnvelope(
                $forecast,
                $config->preRiskModel
            );
        }

        // 3️⃣ Hybrid Compare (MUST use ORIGINAL forecast logic)
        $hybrid = $this->hybridService->compareForecastToBudget(
            $snapshots,
            $metric,
            $futurePeriods,
            $strategy,
            $budgetVersion,
            $parameter
        );

        // 4️⃣ Optional Post-Hybrid Risk (NO MUTATION)
        $postRisk = null;

        if ($config->applyPostHybridRisk && $config->postHybridRiskModel !== null) {
            $postRisk = $this->riskService->generateRiskEnvelope(
                $forecast,
                $config->postHybridRiskModel
            );
        }

        return new ScenarioChainResultDTO(
            forecast: $forecast,
            preRisk: $preRisk,
            hybrid: $hybrid,
            postRisk: $postRisk
        );
    }
}
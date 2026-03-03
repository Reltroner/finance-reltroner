<?php
// app/Services/Accounting/Chaining/ScenarioChainResultDTO.php

namespace App\Services\Accounting\Chaining;

use App\Services\Accounting\Analytics\Forecast\ForecastDTO;
use App\Services\Accounting\Hybrid\ForecastVsBudgetDTO;
use App\Services\Accounting\Risk\RiskEnvelopeDTO;

final class ScenarioChainResultDTO
{
    public function __construct(
        public readonly ForecastDTO $forecast,
        public readonly ?RiskEnvelopeDTO $preRisk,
        public readonly ForecastVsBudgetDTO $hybrid,
        public readonly ?RiskEnvelopeDTO $postRisk
    ) {}
}
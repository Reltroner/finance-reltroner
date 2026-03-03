<?php
// app/Services/Accounting/Chaining/ScenarioChainConfig.php

namespace App\Services\Accounting\Chaining;

use App\Services\Accounting\Risk\RiskModel;
use DomainException;

final class ScenarioChainConfig
{
    public function __construct(
        public readonly ?RiskModel $preRiskModel,
        public readonly ?RiskModel $postHybridRiskModel,
        public readonly bool $applyPreRisk,
        public readonly bool $applyPostHybridRisk
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->applyPreRisk && $this->preRiskModel === null) {
            throw new DomainException(
                'Pre-risk model must be provided when applyPreRisk is true.'
            );
        }

        if ($this->applyPostHybridRisk && $this->postHybridRiskModel === null) {
            throw new DomainException(
                'Post-hybrid risk model must be provided when applyPostHybridRisk is true.'
            );
        }
    }
}
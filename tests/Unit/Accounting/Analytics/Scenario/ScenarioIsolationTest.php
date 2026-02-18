<?php
// tests/Unit/Accounting/Analytics/Scenario/ScenarioIsolationTest.php

namespace Tests\Unit\Accounting\Analytics\Scenario;

use Tests\TestCase;

final class ScenarioIsolationTest extends TestCase
{
    public function test_scenario_layer_has_no_forbidden_imports(): void
    {
        $directory = app_path(
            'Services/Accounting/Analytics/Scenario'
        );

        $files = glob($directory . '/*.php');

        foreach ($files as $file) {

            $code = file_get_contents($file);

            // ❌ No DB usage
            $this->assertStringNotContainsString('DB::', $code);
            $this->assertStringNotContainsString(
                'use Illuminate\\Support\\Facades\\DB',
                $code
            );

            // ❌ No Eloquent Models
            $this->assertStringNotContainsString(
                'use App\\Models',
                $code
            );

            // ❌ No Snapshot layer access
            $this->assertStringNotContainsString(
                'SnapshotGenerationService',
                $code
            );
            $this->assertStringNotContainsString(
                'SnapshotQueryService',
                $code
            );
            $this->assertStringNotContainsString(
                'LedgerStateHasher',
                $code
            );

            // ❌ No Read Layer access
            $this->assertStringNotContainsString(
                'TrialBalanceService',
                $code
            );
            $this->assertStringNotContainsString(
                'ProfitLossService',
                $code
            );
            $this->assertStringNotContainsString(
                'BalanceSheetService',
                $code
            );

            // ❌ No Transaction mutation layer
            $this->assertStringNotContainsString(
                'TransactionService',
                $code
            );
            $this->assertStringNotContainsString(
                'TransactionGuard',
                $code
            );

            // ❌ No Forecast internal recalculation (must consume DTO only)
            $this->assertStringNotContainsString(
                'calculateOLS',
                $code
            );
            $this->assertStringNotContainsString(
                'MovingAverage',
                $code
            );

        }
    }
}

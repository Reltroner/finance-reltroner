<?php
// tests/Unit/Accounting/Analytics/Forecast/ForecastIsolationTest.php

namespace Tests\Unit\Accounting\Analytics\Forecast;

use Tests\TestCase;

final class ForecastIsolationTest extends TestCase
{
    public function test_forecast_namespace_has_no_forbidden_dependencies(): void
    {
        $directory = app_path(
            'Services/Accounting/Analytics/Forecast'
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

            // ❌ No Eloquent models
            $this->assertStringNotContainsString('use App\\Models', $code);

            // ❌ No read layer access
            $this->assertStringNotContainsString('TrialBalanceService', $code);
            $this->assertStringNotContainsString('ProfitLossService', $code);
            $this->assertStringNotContainsString('BalanceSheetService', $code);

            // ❌ No snapshot mutation layer access
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

            // ❌ No transaction/mutation layer
            $this->assertStringNotContainsString(
                'TransactionService',
                $code
            );
            $this->assertStringNotContainsString(
                'TransactionGuard',
                $code
            );

            // ❌ No KPI internal recomputation
            $this->assertStringNotContainsString(
                'FinancialKPIService',
                $code
            );
        }
    }
}

<?php
// tests/Regression/KPIIsolationTest.php
namespace Tests\Regression;

use Tests\TestCase;

class KPIIsolationTest extends TestCase
{
    public function test_financial_kpi_service_has_no_database_usage(): void
    {
        $path = app_path(
            'Services/Accounting/Analytics/FinancialKPIService.php'
        );

        $this->assertFileExists($path);

        $content = file_get_contents($path);

        // Must not access DB facade
        $this->assertStringNotContainsString(
            'DB::',
            $content,
            'FinancialKPIService must not access database.'
        );

        // Must not import Eloquent Models
        $this->assertStringNotContainsString(
            'use App\Models',
            $content,
            'FinancialKPIService must not use Eloquent models.'
        );
    }

    public function test_financial_kpi_service_has_no_forbidden_imports(): void
    {
        $path = app_path(
            'Services/Accounting/Analytics/FinancialKPIService.php'
        );

        $content = file_get_contents($path);

        $forbidden = [
            'TransactionService',
            'TransactionGuard',
            'PeriodClosingService',
            'TrialBalanceService',
            'ProfitLossService',
            'BalanceSheetService',
            'SnapshotGenerationService',
            'SnapshotQueryService',
        ];

        foreach ($forbidden as $class) {
            $this->assertStringNotContainsString(
                $class,
                $content,
                "FinancialKPIService must not depend on {$class}."
            );
        }
    }

    public function test_kpi_dto_has_no_database_or_mutation_dependency(): void
    {
        $path = app_path(
            'Services/Accounting/Analytics/KPIDTO.php'
        );

        $content = file_get_contents($path);

        $this->assertStringNotContainsString(
            'DB::',
            $content,
            'KPIDTO must not access database.'
        );

        $this->assertStringNotContainsString(
            'use App\Models',
            $content,
            'KPIDTO must not use Eloquent models.'
        );
    }
}

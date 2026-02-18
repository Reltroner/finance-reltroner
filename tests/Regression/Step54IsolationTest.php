<?php
// tests/Regression/Step54IsolationTest.php

namespace Tests\Regression;

use Tests\TestCase;
use ReflectionClass;
use App\Services\Accounting\Snapshot\SnapshotGenerationService;
use App\Services\Accounting\Snapshot\SnapshotQueryService;

class Step54IsolationTest extends TestCase
{
    public function test_snapshot_generation_service_does_not_depend_on_mutation_layer(): void
    {
        $reflection = new ReflectionClass(SnapshotGenerationService::class);

        $constructor = $reflection->getConstructor();
        $parameters = $constructor?->getParameters() ?? [];

        foreach ($parameters as $param) {
            $type = $param->getType();
            if ($type) {
                $this->assertStringNotContainsString(
                    'TransactionService',
                    $type->getName(),
                    'SnapshotGenerationService must not depend on TransactionService.'
                );

                $this->assertStringNotContainsString(
                    'TransactionGuard',
                    $type->getName(),
                    'SnapshotGenerationService must not depend on TransactionGuard.'
                );

                $this->assertStringNotContainsString(
                    'PeriodClosingService',
                    $type->getName(),
                    'SnapshotGenerationService must not depend on PeriodClosingService.'
                );
            }
        }
    }

    public function test_snapshot_query_service_does_not_depend_on_mutation_layer(): void
    {
        $reflection = new ReflectionClass(SnapshotQueryService::class);

        $constructor = $reflection->getConstructor();
        $parameters = $constructor?->getParameters() ?? [];

        foreach ($parameters as $param) {
            $type = $param->getType();
            if ($type) {
                $this->assertStringNotContainsString(
                    'TransactionService',
                    $type->getName(),
                    'SnapshotQueryService must not depend on TransactionService.'
                );

                $this->assertStringNotContainsString(
                    'TransactionGuard',
                    $type->getName(),
                    'SnapshotQueryService must not depend on TransactionGuard.'
                );

                $this->assertStringNotContainsString(
                    'PeriodClosingService',
                    $type->getName(),
                    'SnapshotQueryService must not depend on PeriodClosingService.'
                );
            }
        }
    }

    public function test_snapshot_namespace_does_not_import_mutation_services(): void
    {
        $snapshotPath = app_path('Services/Accounting/Snapshot');

        $forbidden = [
            'TransactionService',
            'TransactionGuard',
            'PeriodClosingService',
        ];

        $files = scandir($snapshotPath);

        foreach ($files as $file) {

            if (!str_ends_with($file, '.php')) {
                continue;
            }

            $content = file_get_contents($snapshotPath . '/' . $file);

            foreach ($forbidden as $forbiddenClass) {
                $this->assertStringNotContainsString(
                    $forbiddenClass,
                    $content,
                    "Snapshot layer must not reference {$forbiddenClass}."
                );
            }
        }
    }
}

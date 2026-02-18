<?php
// tests/Regression/Step54FreezeIntegrityTest.php

namespace Tests\Regression;

use Tests\TestCase;

class Step54FreezeIntegrityTest extends TestCase
{
    /**
     * Read Core = Layer 0–5 (must remain snapshot-agnostic)
     */
    private array $frozenReadCoreFiles = [
        'app/Services/Accounting/Read/TrialBalanceService.php',
        'app/Services/Accounting/Read/Statements/ProfitLossService.php',
        'app/Services/Accounting/Read/Statements/BalanceSheetService.php',
        'app/Services/Accounting/Read/Statements/Comparative/ComparativeBalanceSheetService.php',
        'app/Services/Accounting/Read/Statements/Comparative/ComparativeProfitLossService.php',
    ];

    public function test_read_core_services_remain_snapshot_agnostic(): void
    {
        foreach ($this->frozenReadCoreFiles as $file) {

            $this->assertFileExists(
                base_path($file),
                "Frozen read core file missing: {$file}"
            );

            $content = file_get_contents(base_path($file));

            /**
             * 🔒 Layer Boundary Enforcement
             *
             * Read layer must NOT:
             * - Reference SnapshotGenerationService
             * - Reference SnapshotDTO
             * - Reference snapshot table
             * - Import Snapshot namespace
             */

            $forbiddenReferences = [
                'SnapshotGenerationService',
                'SnapshotDTO',
                'SnapshotQueryService',
                'financial_snapshots',
                'App\\Services\\Accounting\\Snapshot',
            ];

            foreach ($forbiddenReferences as $forbidden) {

                $this->assertStringNotContainsString(
                    $forbidden,
                    $content,
                    "Read core service illegally references snapshot layer: {$file} ({$forbidden})"
                );
            }
        }
    }
}

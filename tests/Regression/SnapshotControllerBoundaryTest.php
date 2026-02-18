<?php
// tests/Regression/SnapshotControllerBoundaryTest.php
namespace Tests\Regression;

use Tests\TestCase;

class SnapshotControllerBoundaryTest extends TestCase
{
    public function test_snapshot_controller_contains_no_business_logic(): void
    {
        $path = app_path('Http/Controllers/Reports/SnapshotController.php');

        $this->assertFileExists($path);

        $content = file_get_contents($path);

        // Must not access DB directly
        $this->assertStringNotContainsString(
            'DB::',
            $content,
            'SnapshotController must not access DB directly.'
        );

        // Must not contain retry logic
        $this->assertStringNotContainsString(
            'while',
            $content,
            'SnapshotController must not contain retry logic.'
        );

        // Must not contain hashing logic
        $this->assertStringNotContainsString(
            'hash(',
            $content,
            'SnapshotController must not compute hashes.'
        );

        // Must not reference mutation services
        $forbidden = [
            'TransactionService',
            'TransactionGuard',
            'PeriodClosingService',
        ];

        foreach ($forbidden as $service) {
            $this->assertStringNotContainsString(
                $service,
                $content,
                "SnapshotController must not reference {$service}."
            );
        }
    }

    public function test_snapshot_controller_uses_multi_statement_service(): void
    {
        $path = app_path('Http/Controllers/Reports/SnapshotController.php');
        $content = file_get_contents($path);

        $this->assertStringContainsString(
            'MultiStatementSnapshotService',
            $content,
            'SnapshotController must delegate to MultiStatementSnapshotService.'
        );
    }
}

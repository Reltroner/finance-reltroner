<?php
// tests/Unit/Accounting/Risk/RiskEnvelopeIsolationTest.php
namespace Tests\Unit\Accounting\Risk;

use Tests\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class RiskEnvelopeIsolationTest extends TestCase
{
    public function test_risk_folder_has_no_forbidden_imports(): void
    {
        $path = app_path('Services/Accounting/Risk');

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path)
        );

        $forbidden = [
            'LedgerQueryService',
            'SnapshotReader',
            'BudgetRepository',
            'TransactionService',
            'DB::',
        ];

        foreach ($iterator as $file) {

            if (!$file->isFile()) {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            foreach ($forbidden as $term) {
                $this->assertStringNotContainsString(
                    $term,
                    $content,
                    "Forbidden dependency found: {$term}"
                );
            }
        }
    }
}
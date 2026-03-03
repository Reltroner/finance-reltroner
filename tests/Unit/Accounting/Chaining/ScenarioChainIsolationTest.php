<?php
// tests/Unit/Accounting/Chaining/ScenarioChainIsolationTest.php
namespace Tests\Unit\Accounting\Chaining;

use Tests\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ScenarioChainIsolationTest extends TestCase
{
    public function test_chaining_folder_has_no_forbidden_imports(): void
    {
        $path = app_path('Services/Accounting/Chaining');

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path)
        );

        $forbidden = [
            'LedgerQueryService',
            'SnapshotReader',
            'BudgetRepository',
            'TransactionService',
            'DB::',
            'Illuminate\\Database',
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
                    "Forbidden dependency found in chaining layer: {$term}"
                );
            }
        }
    }
}
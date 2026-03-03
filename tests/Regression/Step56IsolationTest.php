<?php
// tests/Regression/Step56IsolationTest.php
namespace Tests\Regression;

use Tests\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Step56IsolationTest extends TestCase
{
    private array $guardedPaths;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guardedPaths = [
            base_path('app/Services/Accounting/Forecasting'),
            base_path('app/Services/Accounting/Hybrid'),
        ];
    }

    public function test_step56_layers_have_no_forbidden_dependencies(): void
    {
        $forbidden = [
            // Ledger / Write Layer
            'LedgerQueryService',
            'TransactionService',
            'TransactionDetail',
            'FiscalPeriodLockService',
            'PeriodClosingService',

            // Snapshot mutation
            'SnapshotGenerationService',

            // Budget mutation layer
            'BudgetVsActualService',

            // DB / ORM
            'DB::',
            'Illuminate\\Database',
            'Eloquent',
            'Model',
            '->save(',
            '->update(',
            '->delete(',
            'insert(',

            // Time-based non-determinism
            'now(',
            'time(',
        ];

        foreach ($this->guardedPaths as $path) {

            $files = $this->getPhpFiles($path);

            foreach ($files as $file) {

                $contents = file_get_contents($file);

                foreach ($forbidden as $keyword) {

                    $this->assertStringNotContainsString(
                        $keyword,
                        $contents,
                        "Forbidden dependency '{$keyword}' detected in {$file}"
                    );
                }
            }
        }
    }

    private function getPhpFiles(string $directory): array
    {
        $rii = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        $files = [];

        foreach ($rii as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
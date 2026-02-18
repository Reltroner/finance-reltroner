<?php
// tests/Regression/SnapshotIsolationGuardTest.php

namespace Tests\Regression;

use Tests\TestCase;

class SnapshotIsolationGuardTest extends TestCase
{
    public function test_snapshot_layer_does_not_reference_mutation_services(): void
    {
        $snapshotPath = app_path('Services/Accounting/Snapshot');

        $files = $this->getPhpFiles($snapshotPath);

        foreach ($files as $file) {

            $content = file_get_contents($file);

            $this->assertStringNotContainsString(
                'TransactionService',
                $content,
                "Snapshot layer illegally references TransactionService: {$file}"
            );

            $this->assertStringNotContainsString(
                'TransactionGuard',
                $content,
                "Snapshot layer illegally references TransactionGuard: {$file}"
            );

            $this->assertStringNotContainsString(
                'PeriodClosingService',
                $content,
                "Snapshot layer illegally references PeriodClosingService: {$file}"
            );
        }
    }

    private function getPhpFiles(string $directory): array
    {
        $rii = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        $files = [];

        foreach ($rii as $file) {
            if (!$file->isDir() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}

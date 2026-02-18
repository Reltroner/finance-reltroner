<?php
// tests/Regression/ReadLayerBoundaryTest.php

namespace Tests\Regression;

use Tests\TestCase;

class ReadLayerBoundaryTest extends TestCase
{
    public function test_read_layer_does_not_reference_snapshot_or_analytics(): void
    {
        $readPath = app_path('Services/Accounting/Read');

        $files = $this->getPhpFiles($readPath);

        foreach ($files as $file) {

            $content = file_get_contents($file);

            $this->assertStringNotContainsString(
                'use App\\Services\\Accounting\\Snapshot',
                $content,
                "Read layer illegally references Snapshot: {$file}"
            );

            $this->assertStringNotContainsString(
                'use App\\Services\\Accounting\\Analytics',
                $content,
                "Read layer illegally references Analytics: {$file}"
            );

            $this->assertStringNotContainsString(
                'use App\\Services\\Accounting\\Forecast',
                $content,
                "Read layer illegally references Forecast: {$file}"
            );

            $this->assertStringNotContainsString(
                'use App\\Services\\Accounting\\Scenario',
                $content,
                "Read layer illegally references Scenario: {$file}"
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

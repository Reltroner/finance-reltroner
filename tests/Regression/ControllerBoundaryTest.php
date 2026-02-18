<?php
// tests/Regression/ControllerBoundaryTest.php
namespace Tests\Regression;

use Tests\TestCase;

class ControllerBoundaryTest extends TestCase
{
    public function test_report_controllers_do_not_reference_read_layer(): void
    {
        $path = app_path('Http/Controllers/Reports');

        $files = $this->getPhpFiles($path);

        foreach ($files as $file) {

            $content = file_get_contents($file);

            $this->assertStringNotContainsString(
                'use App\\Services\\Accounting\\Read',
                $content,
                "Controller illegally references Read layer: {$file}"
            );

            $this->assertStringNotContainsString(
                'TrialBalanceService',
                $content,
                "Controller illegally references TrialBalanceService: {$file}"
            );

            $this->assertStringNotContainsString(
                'ProfitLossService',
                $content,
                "Controller illegally references ProfitLossService: {$file}"
            );

            $this->assertStringNotContainsString(
                'LedgerQueryService',
                $content,
                "Controller illegally references LedgerQueryService: {$file}"
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

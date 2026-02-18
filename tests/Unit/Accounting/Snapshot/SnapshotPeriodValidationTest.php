<?php
// tests/Unit/Accounting/Snapshot/SnapshotPeriodValidationTest.php
namespace Tests\Unit\Accounting\Snapshot;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Accounting\Snapshot\SnapshotGenerationService;
use DomainException;

class SnapshotPeriodValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_throws_domain_exception_when_period_not_found(): void
    {
        $service = app(SnapshotGenerationService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Fiscal period not found.');

        $service->generate(999999); // non-existent ID
    }
}

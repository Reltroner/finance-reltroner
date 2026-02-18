<?php
// tests/Unit/Accounting/Analytics/Projection/MissingMetricValidationTest.php
namespace Tests\Unit\Accounting\Analytics\Projection;

use Tests\TestCase;
use DomainException;
use DateTimeImmutable;
use App\Services\Accounting\Analytics\KPIDTO;
use App\Services\Accounting\Analytics\Projection\PeriodTrendService;

class MissingMetricValidationTest extends TestCase
{
    public function test_missing_metric_in_snapshot_throws_exception(): void
    {
        $service = new PeriodTrendService();

        $snapshot1 = new KPIDTO(
            snapshotId: 'snap-1',
            fiscalPeriodId: 1,
            version: 1,
            sourceHash: 'hash-1',
            payloadHash: 'payload-1',
            kpis: [
                'net_margin_percent' => 12.5
            ],
            computedAt: new DateTimeImmutable()
        );

        $snapshot2 = new KPIDTO(
            snapshotId: 'snap-2',
            fiscalPeriodId: 2,
            version: 2,
            sourceHash: 'hash-2',
            payloadHash: 'payload-2',
            kpis: [
                // ⚠ Missing 'net_margin_percent'
                'gross_margin_percent' => 40.0
            ],
            computedAt: new DateTimeImmutable()
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(
            'Missing metric in snapshot: net_margin_percent'
        );

        $service->buildTrend(
            'net_margin_percent',
            [$snapshot1, $snapshot2]
        );
    }
}

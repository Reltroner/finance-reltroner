<?php
// tests/Unit/Accounting/Analytics/Projection/GapValidationTest.php
namespace Tests\Unit\Accounting\Analytics\Projection;

use Tests\TestCase;
use App\Services\Accounting\Analytics\Projection\PeriodTrendService;
use App\Services\Accounting\Analytics\KPIDTO;
use DateTimeImmutable;
use DomainException;

class GapValidationTest extends TestCase
{
    private function makeKPI(int $period): KPIDTO
    {
        return new KPIDTO(
            snapshotId: "snap-{$period}",
            fiscalPeriodId: $period,
            version: 1,
            sourceHash: "hash-{$period}",
            payloadHash: "payload-{$period}",
            kpis: ['net_margin_percent' => 10.0],
            computedAt: new DateTimeImmutable()
        );
    }

    public function test_missing_period_gap_throws_exception(): void
    {
        $service = new PeriodTrendService();

        $this->expectException(DomainException::class);

        $service->buildTrend('net_margin_percent', [
            $this->makeKPI(1),
            $this->makeKPI(3),
        ]);
    }
}

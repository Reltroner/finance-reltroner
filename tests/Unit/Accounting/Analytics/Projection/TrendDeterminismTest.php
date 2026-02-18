<?php
// tests/Unit/Accounting/Analytics/Projection/TrendDeterminismTest.php
namespace Tests\Unit\Accounting\Analytics\Projection;

use Tests\TestCase;
use App\Services\Accounting\Analytics\Projection\PeriodTrendService;
use App\Services\Accounting\Analytics\KPIDTO;
use DateTimeImmutable;

class TrendDeterminismTest extends TestCase
{
    private function makeKPI(int $period, float $value): KPIDTO
    {
        return new KPIDTO(
            snapshotId: "snap-{$period}",
            fiscalPeriodId: $period,
            version: 1,
            sourceHash: "hash-{$period}",
            payloadHash: "payload-{$period}",
            kpis: ['net_margin_percent' => $value],
            computedAt: new DateTimeImmutable()
        );
    }

    public function test_trend_is_deterministic(): void
    {
        $service = new PeriodTrendService();

        $kpis = [
            $this->makeKPI(1, 10.0),
            $this->makeKPI(2, 20.0),
            $this->makeKPI(3, 30.0),
        ];

        $trend1 = $service->buildTrend('net_margin_percent', $kpis);
        $trend2 = $service->buildTrend('net_margin_percent', $kpis);

        $this->assertEquals($trend1->toArray(), $trend2->toArray());
    }
}

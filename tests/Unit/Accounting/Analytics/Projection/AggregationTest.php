<?php
// tests/Unit/Accounting/Analytics/Projection/AggregationTest.php
namespace Tests\Unit\Accounting\Analytics\Projection;

use Tests\TestCase;
use App\Services\Accounting\Analytics\Projection\MultiPeriodAggregationService;
use App\Services\Accounting\Analytics\KPIDTO;
use DateTimeImmutable;

class AggregationTest extends TestCase
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

    public function test_aggregation_metrics_are_correct(): void
    {
        $service = new MultiPeriodAggregationService();

        $kpis = [
            $this->makeKPI(1, 10),
            $this->makeKPI(2, 20),
            $this->makeKPI(3, 30),
        ];

        $result = $service->aggregate('net_margin_percent', $kpis);

        $this->assertEquals(20.0, $result['mean']);
        $this->assertEquals(20.0, $result['median']);
        $this->assertEquals(10.0, $result['min']);
        $this->assertEquals(30.0, $result['max']);
        $this->assertEquals(round(sqrt(((100+0+100)/3)),4), $result['stddev']);
    }
}

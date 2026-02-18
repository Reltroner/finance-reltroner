<?php
// app/Http/Controllers/Reports/KPIController.php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Accounting\Snapshot\SnapshotQueryService;
use App\Services\Accounting\Analytics\FinancialKPIService;
use Illuminate\Http\JsonResponse;
use DomainException;

final class KPIController extends Controller
{
    public function __construct(
        private SnapshotQueryService $snapshotQuery,
        private FinancialKPIService $kpiService
    ) {}

    public function show(int $fiscalPeriodId, int $version = 1): JsonResponse
    {
        $snapshot = $this->snapshotQuery
            ->findAggregateByPeriodAndVersion($fiscalPeriodId, $version);

        if (!$snapshot) {
            throw new DomainException(
                'Snapshot not found for KPI computation.'
            );
        }

        $kpi = $this->kpiService->compute(
            current: $snapshot,
            previous: null // or require explicitly if revenue growth enforced
        );

        return response()->json([
            'snapshot_id' => $kpi->snapshotId(),
            'fiscal_period_id' => $kpi->fiscalPeriodId(),
            'version' => $kpi->version(),
            'payload_hash' => $kpi->payloadHash(),
            'kpis' => $kpi->kpis(),
            'generated_at' => $kpi->computedAt()->format('c'),
        ]);
    }
}

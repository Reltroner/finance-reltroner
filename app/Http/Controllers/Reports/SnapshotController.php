<?php
// app/Http/Controllers/Reports/SnapshotController.php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Accounting\Snapshot\SnapshotQueryService;
use Illuminate\Http\JsonResponse;
use DomainException;

final class SnapshotController extends Controller
{
    public function __construct(
        private SnapshotQueryService $queryService
    ) {}

    /**
     * Export immutable snapshot.
     * 5.4.h compliant: read-only boundary.
     */
    public function show(int $fiscalPeriodId, int $version): JsonResponse
    {
        $snapshot = $this->queryService->get($fiscalPeriodId, $version);

        if (!$snapshot) {
            throw new DomainException(
                'Snapshot not found for given fiscal period and version.'
            );
        }

        return response()->json([
            'id' => $snapshot->id(),
            'fiscal_period_id' => $snapshot->fiscalPeriodId(),
            'version' => $snapshot->version(),
            'payload' => $snapshot->payload(),
            'payload_hash' => $snapshot->payloadHash(),
            'source_hash' => $snapshot->sourceHash(),
            'created_at' => $snapshot->createdAt()->format(DATE_ATOM),
        ]);
    }
}

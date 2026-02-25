<?php
// app/Services/Accounting/Budget/SnapshotPayloadAdapter.php
namespace App\Services\Accounting\Budget;

use App\Services\Accounting\Snapshot\SnapshotQueryService;
use App\Services\Accounting\Budget\Contracts\SnapshotPayloadReader;

final class SnapshotPayloadAdapter implements SnapshotPayloadReader
{
    public function __construct(
        private readonly SnapshotQueryService $snapshotService
    ) {}

    public function getPayload(int $fiscalPeriodId, int $version): array
    {
        $dto = $this->snapshotService->get($fiscalPeriodId, $version);

        return $dto->payload;
    }
}
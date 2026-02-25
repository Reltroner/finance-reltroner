<?php
// app/Services/Accounting/Budget/Contracts/SnapshotPayloadReader.php
namespace App\Services\Accounting\Budget\Contracts;

interface SnapshotPayloadReader
{
    public function getPayload(
        int $fiscalPeriodId,
        int $version
    ): array;
}
<?php
// app/Services/Accounting/Snapshot/SnapshotQueryService.php
namespace App\Services\Accounting\Snapshot;

use Illuminate\Support\Facades\DB;
use DateTimeImmutable;
use RuntimeException;
use App\Services\Accounting\Snapshot\Contracts\SnapshotExistenceChecker;

final class SnapshotQueryService implements SnapshotExistenceChecker
{
    public function get(int $fiscalPeriodId, int $version): SnapshotDTO
    {
        $record = DB::table('financial_snapshots')
            ->where('fiscal_period_id', $fiscalPeriodId)
            ->where('version', $version)
            ->first();

        if (!$record) {
            throw new RuntimeException('Snapshot not found.');
        }

        $payload = json_decode($record->payload_json, true);

        if (!is_array($payload)) {
            throw new RuntimeException('Corrupted snapshot payload.');
        }

        return new SnapshotDTO(
            id: $record->id,
            fiscalPeriodId: (int) $record->fiscal_period_id,
            version: (int) $record->version,
            payload: $payload,
            payloadHash: $record->payload_hash,
            sourceHash: $record->source_hash,
            createdAt: new DateTimeImmutable($record->created_at)
        );
    }

    public function exists(string $snapshotVersion): bool
    {
        return \DB::table('financial_snapshots')
            ->where('version', $snapshotVersion)
            ->exists();
    }

    
}

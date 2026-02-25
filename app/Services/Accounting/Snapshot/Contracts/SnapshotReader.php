<?php
// app/Services/Accounting/Snapshot/Contracts/SnapshotReader.php
declare(strict_types=1);

namespace App\Services\Accounting\Snapshot\Contracts;

interface SnapshotReader
{
    public function get(string $snapshotVersion): array;
}
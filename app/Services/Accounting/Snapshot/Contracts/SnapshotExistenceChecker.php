<?php
// app/Services/Accounting/Snapshot/Contracts/SnapshotExistenceChecker.php
declare(strict_types=1);

namespace App\Services\Accounting\Snapshot\Contracts;

interface SnapshotExistenceChecker
{
    public function exists(string $snapshotVersion): bool;
}
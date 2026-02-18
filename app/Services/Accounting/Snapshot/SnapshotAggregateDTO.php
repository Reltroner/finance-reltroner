<?php
// app/Services/Accounting/Snapshot/SnapshotAggregateDTO.php
namespace App\Services\Accounting\Snapshot;

use DateTimeImmutable;

final class SnapshotAggregateDTO
{
    public function __construct(
        private string $snapshotId,
        private int $fiscalPeriodId,
        private int $version,
        private array $statements,
        private string $payloadHash,
        private string $sourceHash,
        private DateTimeImmutable $createdAt
    ) {}

    public function snapshotId(): string
    {
        return $this->snapshotId;
    }

    public function fiscalPeriodId(): int
    {
        return $this->fiscalPeriodId;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function statements(): array
    {
        return $this->statements;
    }

    public function payloadHash(): string
    {
        return $this->payloadHash;
    }

    public function sourceHash(): string
    {
        return $this->sourceHash;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}

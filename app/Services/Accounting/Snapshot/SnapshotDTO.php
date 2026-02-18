<?php
// app/Services/Accounting/Snapshot/SnapshotDTO.php
namespace App\Services\Accounting\Snapshot;

use DateTimeImmutable;

final class SnapshotDTO
{
    private string $id;
    private int $fiscalPeriodId;
    private int $version;
    private array $payload;
    private string $payloadHash;
    private string $sourceHash;
    private DateTimeImmutable $createdAt;

    public function __construct(
        string $id,
        int $fiscalPeriodId,
        int $version,
        array $payload,
        string $payloadHash,
        string $sourceHash,
        DateTimeImmutable $createdAt
    ) {
        $this->id = $id;
        $this->fiscalPeriodId = $fiscalPeriodId;
        $this->version = $version;
        $this->payload = $payload;
        $this->payloadHash = $payloadHash;
        $this->sourceHash = $sourceHash;
        $this->createdAt = $createdAt;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function fiscalPeriodId(): int
    {
        return $this->fiscalPeriodId;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function payload(): array
    {
        return $this->payload;
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

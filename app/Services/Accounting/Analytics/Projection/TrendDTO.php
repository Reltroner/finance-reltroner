<?php
// app/Services/Accounting/Analytics/Projection/TrendDTO.php
namespace App\Services\Accounting\Analytics\Projection;

use DateTimeImmutable;

final class TrendDTO
{
    public function __construct(
        private string $metric,
        private array $periods,
        private array $values,
        private array $snapshotIds,
        private array $sourceHashes,
        private DateTimeImmutable $generatedAt
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $count = count($this->periods);

        if ($count === 0) {
            throw new \DomainException(
                'TrendDTO requires at least one period.'
            );
        }

        if (
            $count !== count($this->values) ||
            $count !== count($this->snapshotIds) ||
            $count !== count($this->sourceHashes)
        ) {
            throw new \DomainException(
                'TrendDTO arrays must have equal length.'
            );
        }
    }

    public function metric(): string
    {
        return $this->metric;
    }

    public function periods(): array
    {
        return $this->periods;
    }

    public function values(): array
    {
        return $this->values;
    }

    public function snapshotIds(): array
    {
        return $this->snapshotIds;
    }

    public function sourceHashes(): array
    {
        return $this->sourceHashes;
    }

    public function generatedAt(): DateTimeImmutable
    {
        return $this->generatedAt;
    }

    public function toArray(): array
    {
        return [
            'metric' => $this->metric,
            'periods' => $this->periods,
            'values' => $this->values,
            'snapshot_ids' => $this->snapshotIds,
            'source_hashes' => $this->sourceHashes,
            'generated_at' => $this->generatedAt->format('c'),
        ];
    }
}

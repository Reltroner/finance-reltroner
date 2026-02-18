<?php
// app/Services/Accounting/Analytics/KPIDTO.php
namespace App\Services\Accounting\Analytics;

use DateTimeImmutable;

final class KPIDTO
{
    public function __construct(
        private string $snapshotId,
        private int $fiscalPeriodId,
        private int $version,
        private string $payloadHash,
        private string $sourceHash,
        private array $kpis,
        private DateTimeImmutable $computedAt
    ) {
        $this->assertValidKPIStructure($kpis);
    }

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

    public function payloadHash(): string
    {
        return $this->payloadHash;
    }

    public function sourceHash(): string
    {
        return $this->sourceHash;
    }

    /**
     * @return array<string, float>
     */
    public function kpis(): array
    {
        return $this->kpis;
    }

    public function computedAt(): DateTimeImmutable
    {
        return $this->computedAt;
    }

    /**
     * Ensure KPI structure is deterministic and numeric.
     */
    private function assertValidKPIStructure(array $kpis): void
    {
        foreach ($kpis as $key => $value) {

            if (!is_string($key)) {
                throw new \DomainException(
                    'KPI key must be string.'
                );
            }

            if (!is_float($value) && !is_int($value)) {
                throw new \DomainException(
                    "KPI value for {$key} must be numeric."
                );
            }
        }
    }
}

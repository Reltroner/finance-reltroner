<?php
// app/Services/Accounting/Snapshot/MultiStatementSnapshotService.php

namespace App\Services\Accounting\Snapshot;

use App\Models\FiscalPeriod;
use DomainException;
use Illuminate\Support\Facades\DB;

final class MultiStatementSnapshotService
{
    public function __construct(
        private SnapshotGenerationService $snapshotGenerationService,
        private LedgerStateHasher $ledgerStateHasher,
        private StatementCohesionVerifier $cohesionVerifier
    ) {}

    public function generate(int $fiscalPeriodId): SnapshotAggregateDTO
    {
        $period = FiscalPeriod::query()->find($fiscalPeriodId);

        if (!$period) {
            throw new DomainException('Fiscal period not found.');
        }

        return DB::transaction(function () use ($period, $fiscalPeriodId) {

            // 1️⃣ Ledger state BEFORE (aligned with new contract)
            $beforeHash = $this->ledgerStateHasher
                ->hashForFiscalPeriod(
                    $period->year,
                    $period->period
                );

            // 2️⃣ Delegate persistence
            $snapshot = $this->snapshotGenerationService
                ->generate($fiscalPeriodId);

            // 3️⃣ Ledger state AFTER
            $afterHash = $this->ledgerStateHasher
                ->hashForFiscalPeriod(
                    $period->year,
                    $period->period
                );

            if ($beforeHash !== $afterHash) {
                throw new DomainException(
                    'Ledger drift detected during multi-statement snapshot generation.'
                );
            }

            $statements = $snapshot->payload();

            // 4️⃣ Cohesion verification
            $this->cohesionVerifier->verify(
                $statements,
                $snapshot->fiscalPeriodId()
            );

            return new SnapshotAggregateDTO(
                snapshotId: $snapshot->id(),
                fiscalPeriodId: $snapshot->fiscalPeriodId(),
                version: $snapshot->version(),
                statements: $statements,
                payloadHash: $snapshot->payloadHash(),
                sourceHash: $snapshot->sourceHash(),
                createdAt: $snapshot->createdAt()
            );
        });
    }
}

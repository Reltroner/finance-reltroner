<?php
// app/Services/Accounting/Snapshot/SnapshotGenerationService.php

namespace App\Services\Accounting\Snapshot;

use App\Models\FiscalPeriod;
use App\Services\Accounting\Read\TrialBalanceService;
use App\Services\Accounting\Read\Statements\ProfitLossService;
use App\Services\Accounting\Read\Statements\BalanceSheetService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use DateTimeImmutable;
use RuntimeException;

final class SnapshotGenerationService
{
    public function __construct(
        private TrialBalanceService $trialBalanceService,
        private ProfitLossService $profitLossService,
        private BalanceSheetService $balanceSheetService,
        private LedgerStateHasher $ledgerStateHasher,
        private SnapshotVersionCoordinator $versionCoordinator
    ) {}

    /**
     * Generate frozen financial statements bundle from 5.3 read layer.
     *
     * This method does NOT persist.
     * This method does NOT compute version.
     * This method does NOT touch snapshot table.
     */
    private function resolveStatements(FiscalPeriod $period): array
    {
        return [
            'trial_balance' => $this->trialBalanceService
                ->generate($period->year, $period->period)
                ->map(fn($b) => $b->toArray())
                ->values()
                ->toArray(),

            'profit_loss'   => $this->profitLossService
                ->generate($period->year, $period->period)
                ->toArray(),

            'balance_sheet' => $this->balanceSheetService
                ->generate($period->year, $period->period)
                ->toArray(),
        ];
    }

    public function generate(int $fiscalPeriodId): SnapshotDTO
    {
        $period = FiscalPeriod::query()->find($fiscalPeriodId);

        if (!$period) {
            throw new \DomainException('Fiscal period not found.');
        }

        return DB::transaction(function () use ($period, $fiscalPeriodId) {

            // 1️⃣ Invoke frozen read layer correctly
            $statements = $this->resolveStatements($period);

            // 2️⃣ Canonicalize deterministically
            $payload = $this->canonicalizeStructure($statements);

            // 3️⃣ Stable JSON encoding
            $json = $this->stableJsonEncode($payload);

            // 4️⃣ Compute hashes (aligned with read contract)
            $payloadHash = hash('sha256', $json);

            $sourceHash  = $this->ledgerStateHasher
                ->hashForFiscalPeriod(
                    $period->year,
                    $period->period
                );

            // 5️⃣ Retry-safe append-only persistence
            $maxAttempts = 3;
            $attempt = 0;

            while ($attempt < $maxAttempts) {

                $version = $this->versionCoordinator
                    ->nextVersion($fiscalPeriodId);

                $id = (string) Str::uuid();

                try {

                    $inserted = DB::table('financial_snapshots')->insert([
                        'id'               => $id,
                        'fiscal_period_id' => $fiscalPeriodId,
                        'version'          => $version,
                        'payload_json'     => $json,
                        'payload_hash'     => $payloadHash,
                        'source_hash'      => $sourceHash,
                        'created_at'       => now(),
                    ]);

                    if (!$inserted) {
                        throw new RuntimeException('Snapshot persistence failed.');
                    }

                    $stored = DB::table('financial_snapshots')
                        ->where('id', $id)
                        ->first();

                    if (!$stored) {
                        throw new RuntimeException('Snapshot retrieval failed after insert.');
                    }

                    $recomputedHash = hash('sha256', $stored->payload_json);

                    if ($recomputedHash !== $stored->payload_hash) {
                        throw new RuntimeException(
                            'Snapshot hash integrity verification failed.'
                        );
                    }

                    return new SnapshotDTO(
                        id: $id,
                        fiscalPeriodId: $fiscalPeriodId,
                        version: $version,
                        payload: $payload,
                        payloadHash: $payloadHash,
                        sourceHash: $sourceHash,
                        createdAt: new DateTimeImmutable()
                    );

                } catch (QueryException $e) {

                    if ($this->isUniqueConstraintViolation($e)) {
                        $attempt++;

                        if ($attempt >= $maxAttempts) {
                            throw new RuntimeException(
                                'Snapshot version conflict retry limit exceeded.'
                            );
                        }

                        continue;
                    }

                    throw $e;
                }
            }

            throw new RuntimeException('Snapshot generation failed after retries.');
        });
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'UNIQUE')
            || str_contains($message, 'unique')
            || str_contains($message, 'constraint');
    }

    /**
     * Canonicalize structure recursively.
     */
    private function canonicalizeStructure(mixed $data): mixed
    {
        if (is_object($data)) {
            $data = json_decode(json_encode($data), true);
        }

        if (!is_array($data)) {
            return $this->normalizeValue($data);
        }

        // Recursively canonicalize children first
        foreach ($data as $key => $value) {
            $data[$key] = $this->canonicalizeStructure($value);
        }

        // If associative array → sort by keys
        if ($this->isAssociativeArray($data)) {
            ksort($data);
        } else {
            // Indexed array → sort deterministically by JSON value
            usort($data, function ($a, $b) {
                return strcmp(
                    json_encode($a),
                    json_encode($b)
                );
            });
        }

        return $data;
    }

    private function stableJsonEncode(array $data): string
    {
        $json = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new RuntimeException('JSON encoding failed during snapshot generation.');
        }

        return $json;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_numeric($value)) {
            return number_format((float) $value, 2, '.', '');
        }

        return $value;
    }

    private function isAssociativeArray(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}

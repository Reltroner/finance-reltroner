<?php
// app/Services/Accounting/Snapshot/StatementCohesionVerifier.php
namespace App\Services\Accounting\Snapshot;

use DomainException;

final class StatementCohesionVerifier
{
    /**
     * Verify multi-statement cohesion invariants.
     *
     * Invariants:
     * - Required statements must exist.
     * - No duplicate statement keys.
     * - All statements reference same fiscal_period_id (if present).
     */
    public function verify(array $statements, int $expectedFiscalPeriodId): void
    {
        $this->assertRequiredStatementsPresent($statements);
        $this->assertNoDuplicateKeys($statements);
        $this->assertFiscalPeriodConsistency($statements, $expectedFiscalPeriodId);
    }

    private function assertRequiredStatementsPresent(array $statements): void
    {
        $required = [
            'trial_balance',
            'profit_loss',
            'balance_sheet',
        ];

        foreach ($required as $key) {
            if (!array_key_exists($key, $statements)) {
                throw new DomainException(
                    "Missing required statement in snapshot aggregate: {$key}"
                );
            }
        }
    }

    private function assertNoDuplicateKeys(array $statements): void
    {
        $keys = array_keys($statements);

        if (count($keys) !== count(array_unique($keys))) {
            throw new DomainException(
                'Duplicate statement types detected in snapshot aggregate.'
            );
        }
    }

    private function assertFiscalPeriodConsistency(
        array $statements,
        int $expectedFiscalPeriodId
    ): void {
        foreach ($statements as $type => $statement) {

            if (is_object($statement)) {
                $statement = json_decode(json_encode($statement), true);
            }

            if (!is_array($statement)) {
                continue;
            }

            if (isset($statement['fiscal_period_id'])
                && (int)$statement['fiscal_period_id'] !== $expectedFiscalPeriodId
            ) {
                throw new DomainException(
                    "Statement fiscal_period_id mismatch detected in {$type}."
                );
            }
        }
    }
}

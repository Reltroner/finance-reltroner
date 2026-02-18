<?php
// app/Services/Accounting/Snapshot/LedgerStateHasher.php
namespace App\Services\Accounting\Snapshot;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class LedgerStateHasher
{
    public function hashForFiscalPeriod(int $year, int $period): string
    {
        $totals = DB::table('transactions')
            ->where('fiscal_year', $year)
            ->where('fiscal_period', $period)
            ->selectRaw('
                COUNT(*) as transaction_count,
                COALESCE(SUM(total_debit), 0) as total_debit,
                COALESCE(SUM(total_credit), 0) as total_credit
            ')
            ->first();

        if (!$totals) {
            throw new RuntimeException('Unable to compute ledger totals.');
        }

        $detailCount = DB::table('transaction_details')
            ->whereIn('transaction_id', function ($query) use ($year, $period) {
                $query->select('id')
                    ->from('transactions')
                    ->where('fiscal_year', $year)
                    ->where('fiscal_period', $period);
            })
            ->count();

        $canonicalString = sprintf(
            '%s|%s|%d|%d',
            $this->normalizeNumber($totals->total_debit),
            $this->normalizeNumber($totals->total_credit),
            (int) $totals->transaction_count,
            (int) $detailCount
        );

        return hash('sha256', $canonicalString);
    }

    private function normalizeNumber($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}

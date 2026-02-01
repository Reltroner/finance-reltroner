<?php
// app/Services/Accounting/FiscalPeriodLockService.php
namespace App\Services\Accounting;

use App\Models\FiscalPeriod;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FiscalPeriodLockService
{
    /**
     * Lock fiscal period after closing
     */
    public function lock(int $year, int $period, int $userId): FiscalPeriod
    {
        return DB::transaction(function () use ($year, $period, $userId) {

            $periodRow = FiscalPeriod::firstOrCreate(
                ['year' => $year, 'period' => $period],
                ['status' => 'open']
            );

            if ($periodRow->isLocked()) {
                throw new RuntimeException("Fiscal period {$year}-{$period} is already locked.");
            }

            // Must have period closing journal
            $hasClosing = Transaction::where('fiscal_year', $year)
                ->where('fiscal_period', $period)
                ->where('type', Transaction::TYPE_PERIOD_CLOSING)
                ->exists();

            if (!$hasClosing) {
                throw new RuntimeException(
                    "Cannot lock period {$year}-{$period} before period closing."
                );
            }

            $periodRow->update([
                'status'    => 'locked',
                'locked_at' => now(),
                'locked_by' => $userId,
            ]);

            return $periodRow;
        });
    }
}

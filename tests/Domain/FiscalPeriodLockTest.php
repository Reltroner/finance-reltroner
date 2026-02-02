<?php
// tests/Domain/FiscalPeriodLockTest.php
namespace Tests\Domain;

use Tests\Support\AccountingTestCase;
use App\Models\FiscalPeriod;
use App\Services\Accounting\TransactionService;
use DomainException;

class FiscalPeriodLockTest extends AccountingTestCase
{
    public function test_cannot_create_transaction_on_locked_period(): void
    {
        FiscalPeriod::create([
            'year' => 2025,
            'period' => 6,
            'status' => 'locked',
        ]);

        $this->expectException(DomainException::class);

        app(TransactionService::class)->create([
            'date' => '2025-06-15',
            'currency_id' => 1,
            'details' => [
                ['account_id' => 1, 'debit' => 100],
                ['account_id' => 2, 'credit' => 100],
            ],
        ], $this->userId);
    }
}

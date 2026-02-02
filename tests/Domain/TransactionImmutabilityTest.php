<?php
// tests/Domain/TransactionImmutabilityTest.php
namespace Tests\Domain;

use Tests\Support\AccountingTestCase;
use App\Models\Transaction;
use App\Services\Accounting\TransactionService;
use DomainException;

/**
 * ============================================================
 * DOMAIN TEST — TRANSACTION IMMUTABILITY
 * ============================================================
 *
 * Contract verified:
 * - Posted transactions are immutable
 * - Equity journals are immutable
 * - System journals are immutable
 * - Only DRAFT + GENERAL transactions are editable
 *
 * STEP:
 * - 5.2B.2  (Equity & Retained Earnings Contract)
 * - 5.2B.3  (Immutable Journal Rule)
 * - 5.2B.4  (Fiscal Period Lock Enforcement)
 *
 * This test MUST NEVER be removed.
 */
class TransactionImmutabilityTest extends AccountingTestCase
{
    protected TransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TransactionService::class);
    }

    /** @test */
    public function posted_transaction_cannot_be_updated(): void
    {
        $tx = Transaction::factory()->posted()->create();

        $this->expectException(DomainException::class);

        $this->service->update(
            $tx,
            ['description' => 'ILLEGAL UPDATE'],
            $this->userId
        );
    }

    /** @test */
    public function posted_transaction_cannot_be_deleted(): void
    {
        $tx = Transaction::factory()->posted()->create();

        $this->expectException(DomainException::class);

        $this->service->delete($tx);
    }

    /** @test */
    public function equity_journal_is_always_immutable_even_in_draft(): void
    {
        $tx = Transaction::factory()->draft()->create([
            'type' => Transaction::TYPE_EQUITY_OPENING,
        ]);

        $this->assertFalse(
            $tx->isEditable(),
            'Equity journal must never be editable'
        );

        $this->expectException(DomainException::class);

        $this->service->update(
            $tx,
            ['description' => 'ILLEGAL EQUITY MUTATION'],
            $this->userId
        );
    }

    /** @test */
    public function system_journal_is_immutable(): void
    {
        $tx = Transaction::factory()->draft()->create([
            'type' => Transaction::TYPE_SYSTEM_ADJUSTMENT,
        ]);

        $this->assertFalse(
            $tx->isEditable(),
            'System journal must be immutable'
        );

        $this->expectException(DomainException::class);

        $this->service->update(
            $tx,
            ['description' => 'ILLEGAL SYSTEM CHANGE'],
            $this->userId
        );
    }

    /** @test */
    public function draft_general_transaction_is_editable(): void
    {
        $tx = Transaction::factory()->draft()->create([
            'type' => Transaction::TYPE_GENERAL,
        ]);

        $this->assertTrue(
            $tx->isEditable(),
            'Draft general transaction should be editable'
        );

        $updated = $this->service->update(
            $tx,
            ['description' => 'VALID UPDATE'],
            $this->userId
        );

        $this->assertEquals(
            'VALID UPDATE',
            $updated->description
        );
    }

    /** @test */
    public function equity_transaction_cannot_be_deleted(): void
    {
        $tx = Transaction::factory()->create([
            'type'   => Transaction::TYPE_EQUITY_INJECTION,
            'status' => 'draft',
        ]);

        $this->expectException(DomainException::class);

        $this->service->delete($tx);
    }
}

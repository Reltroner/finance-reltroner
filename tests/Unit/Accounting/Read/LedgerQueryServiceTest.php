<?php
// tests/Unit/Accounting/Read/LedgerQueryServiceTest.php

namespace Tests\Unit\Accounting\Read;

use Tests\TestCase;
use App\Models\Transaction;
use App\Services\Accounting\Read\LedgerQueryService;
use Tests\Support\Seeds\ReadOnlyLedgerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LedgerQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reads_ledger_lines_without_mutation(): void
    {
        $data = ReadOnlyLedgerSeeder::seed();

        $service = app(LedgerQueryService::class);

        $lines = $service->getLedgerLines(
            $data['cash']->id,
            $data['period']->year,
            $data['period']->period
        );

        $this->assertCount(1, $lines);

        $line = $lines->first();

        $this->assertEquals($data['cash']->id, $line->accountId);
        $this->assertEquals(1000, $line->debit);
        $this->assertEquals(0, $line->credit);
        $this->assertEquals(1000, $line->amount());
    }

    public function test_transaction_lines_have_sequential_line_numbers(): void
    {
        // ✅ CREATE ONE TRANSACTION THAT OWNS THE LINES
        $tx = Transaction::factory()
            ->forPeriod(2024, 1)
            ->withLines(5)
            ->create([
                'type'             => Transaction::TYPE_SYSTEM_ADJUSTMENT,
                'transaction_type' => Transaction::TYPE_SYSTEM_ADJUSTMENT,
                'status'           => 'posted',
            ]);

        // ✅ QUERY LINES FROM THE SAME AGGREGATE ROOT
        $lines = $tx->details()
            ->orderBy('line_no')
            ->get();

        $this->assertCount(5, $lines);

        $this->assertEquals(
            range(1, $lines->count()),
            $lines->pluck('line_no')->toArray()
        );
    }
}

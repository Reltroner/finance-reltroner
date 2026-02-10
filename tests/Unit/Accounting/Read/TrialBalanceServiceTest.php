<?php
// tests/Unit/Accounting/Read/TrialBalanceServiceTest.php

namespace Tests\Unit\Accounting\Read;

use Tests\TestCase;
use App\Services\Accounting\Read\TrialBalanceService;
use Tests\Support\Seeds\ReadOnlyLedgerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TrialBalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_trial_balance_is_balanced(): void
    {
        $data = ReadOnlyLedgerSeeder::seed();

        $trialBalance = app(TrialBalanceService::class)
            ->generate(
                $data['period']->year,
                $data['period']->period
            );

        $this->assertNotEmpty($trialBalance);

        $netTotal = $trialBalance->sum(
            fn ($balance) => $balance->net()
        );

        // Fundamental accounting invariant
        $this->assertEquals(0, $netTotal);
    }
}

<?php
// tests/Support/AccountingTestCase.php
namespace Tests\Support;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class AccountingTestCase extends TestCase
{
    use RefreshDatabase;

    protected int $userId = 1;

    protected function setUp(): void
    {
        parent::setUp();

        // migrate fresh
        $this->artisan('migrate');

        // seed minimal accounting data
        $this->seed(\Database\Seeders\AccountSeeder::class);
        $this->seed(\Database\Seeders\CurrencySeeder::class);
        $this->seed(\Database\Seeders\CostCenterSeeder::class);
    }
}

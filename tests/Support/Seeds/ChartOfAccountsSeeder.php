<?php
// tests/Support/Seeds/ChartOfAccountsSeeder.php

namespace Tests\Support\Seeds;

use App\Models\Account;

class ChartOfAccountsSeeder
{
    /**
     * Seed minimal, deterministic Chart of Accounts
     * Safe to call multiple times (idempotent).
     */
    public static function seed(): void
    {
        self::asset();
        self::liability();
        self::equity();
        self::income();
        self::expense();
    }

    protected static function asset(): void
    {
        Account::firstOrCreate(
            ['code' => '1000'],
            [
                'name'           => 'Cash',
                'type'           => Account::TYPE_ASSET,
                'normal_balance' => Account::NORMAL_DEBIT,
                'is_active'      => true,
            ]
        );
    }

    protected static function liability(): void
    {
        Account::firstOrCreate(
            ['code' => '2000'],
            [
                'name'           => 'Accounts Payable',
                'type'           => Account::TYPE_LIABILITY,
                'normal_balance' => Account::NORMAL_CREDIT,
                'is_active'      => true,
            ]
        );
    }

    protected static function equity(): void
    {
        Account::firstOrCreate(
            ['code' => '3000'],
            [
                'name'           => 'Retained Earnings',
                'type'           => Account::TYPE_EQUITY,
                'normal_balance' => Account::NORMAL_CREDIT,
                'is_active'      => true,
            ]
        );
    }

    protected static function income(): void
    {
        Account::firstOrCreate(
            ['code' => '4000'],
            [
                'name'           => 'Revenue',
                'type'           => Account::TYPE_INCOME,
                'normal_balance' => Account::NORMAL_CREDIT,
                'is_active'      => true,
            ]
        );
    }

    protected static function expense(): void
    {
        Account::firstOrCreate(
            ['code' => '5000'],
            [
                'name'           => 'Operating Expense',
                'type'           => Account::TYPE_EXPENSE,
                'normal_balance' => Account::NORMAL_DEBIT,
                'is_active'      => true,
            ]
        );
    }
}

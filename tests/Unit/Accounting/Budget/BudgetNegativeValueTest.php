<?php

declare(strict_types=1);

namespace Tests\Unit\Accounting\Budget;

use App\Services\Accounting\Budget\BudgetDefinition;
use DomainException;
use PHPUnit\Framework\TestCase;

class BudgetNegativeValueTest extends TestCase
{
    public function test_negative_value_throws_exception(): void
    {
        $this->expectException(DomainException::class);

        new BudgetDefinition(
            snapshotVersion: 'v1',
            budgetVersion: 1,
            metric: 'revenue',
            period: '2026-01',
            plannedValue: -100
        );
    }
}
<?php
// tests/Unit/Accounting/Budget/BudgetDefinitionValidationTest.php
declare(strict_types=1);

namespace Tests\Unit\Accounting\Budget;

use App\Services\Accounting\Budget\BudgetDefinition;
use DomainException;
use PHPUnit\Framework\TestCase;

class BudgetDefinitionValidationTest extends TestCase
{
    public function test_valid_budget_definition_is_created(): void
    {
        $budget = new BudgetDefinition(
            snapshotVersion: 'v1',
            budgetVersion: 1,
            metric: 'revenue',
            period: '2026-01',
            plannedValue: 1000.123456
        );

        $this->assertSame(1000.1235, $budget->plannedValue);
    }
}
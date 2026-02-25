<?php
// tests/Regression/BudgetAppendOnlyTest.php
declare(strict_types=1);

namespace Tests\Regression;

use App\Models\FinancialBudget;
use DomainException;
use Tests\TestCase;

class BudgetAppendOnlyTest extends TestCase
{
    public function test_update_is_blocked(): void
    {
        $budget = new FinancialBudget();

        $this->expectException(DomainException::class);

        $budget->update(['metric' => 'test']);
    }

    public function test_delete_is_blocked(): void
    {
        $budget = new FinancialBudget();

        $this->expectException(DomainException::class);

        $budget->delete();
    }
}
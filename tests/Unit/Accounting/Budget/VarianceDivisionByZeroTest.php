<?php
// tests/Unit/Accounting/Budget/VarianceDivisionByZeroTest.php
declare(strict_types=1);

namespace Tests\Unit\Accounting\Budget;

use App\Services\Accounting\Budget\VarianceEngine;
use DomainException;
use PHPUnit\Framework\TestCase;

class VarianceDivisionByZeroTest extends TestCase
{
    public function test_budget_zero_throws_exception(): void
    {
        $this->expectException(DomainException::class);

        VarianceEngine::compute(
            metric: 'revenue',
            period: '2026-01',
            actual: 1000,
            budget: 0
        );
    }
}
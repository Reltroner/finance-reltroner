<?php
// tests/Unit/Accounting/Budget/VarianceDeterminismTest.php
declare(strict_types=1);

namespace Tests\Unit\Accounting\Budget;

use App\Services\Accounting\Budget\VarianceEngine;
use PHPUnit\Framework\TestCase;

class VarianceDeterminismTest extends TestCase
{
    public function test_same_input_produces_identical_output(): void
    {
        $a = VarianceEngine::compute(
            metric: 'revenue',
            period: '2026-01',
            actual: 1500.123456,
            budget: 1000.123456
        );

        $b = VarianceEngine::compute(
            metric: 'revenue',
            period: '2026-01',
            actual: 1500.123456,
            budget: 1000.123456
        );

        $this->assertSame($a->absoluteVariance, $b->absoluteVariance);
        $this->assertSame($a->percentageVariance, $b->percentageVariance);
    }
}
<?php
// tests/Unit/Accounting/Budget/VarianceRoundingTest.php
declare(strict_types=1);

namespace Tests\Unit\Accounting\Budget;

use App\Services\Accounting\Budget\VarianceEngine;
use PHPUnit\Framework\TestCase;

class VarianceRoundingTest extends TestCase
{
    public function test_rounding_is_4_decimal(): void
    {
        $dto = VarianceEngine::compute(
            metric: 'revenue',
            period: '2026-01',
            actual: 1000.123456,
            budget: 900.123456
        );

        $this->assertSame(
            round($dto->absoluteVariance, 4),
            $dto->absoluteVariance
        );

        $this->assertSame(
            round($dto->percentageVariance, 4),
            $dto->percentageVariance
        );
    }
}
<?php
// tests/Unit/Accounting/Risk/RiskModelValidationTest.php
namespace Tests\Unit\Accounting\Risk;

use Tests\TestCase;
use App\Services\Accounting\Risk\RiskModel;
use DomainException;

class RiskModelValidationTest extends TestCase
{
    public function test_valid_model_is_created(): void
    {
        $model = new RiskModel(
            volatilityPercent: 0.1,
            compressionFactor: 0.9,
            shockMultiplier: 1.2,
            cap: 1000,
            floor: 10
        );

        $this->assertInstanceOf(RiskModel::class, $model);
    }

    public function test_negative_volatility_throws(): void
    {
        $this->expectException(DomainException::class);

        new RiskModel(
            volatilityPercent: -0.1,
            compressionFactor: 1,
            shockMultiplier: 1
        );
    }

    public function test_zero_compression_throws(): void
    {
        $this->expectException(DomainException::class);

        new RiskModel(
            volatilityPercent: 0.1,
            compressionFactor: 0,
            shockMultiplier: 1
        );
    }

    public function test_zero_shock_throws(): void
    {
        $this->expectException(DomainException::class);

        new RiskModel(
            volatilityPercent: 0.1,
            compressionFactor: 1,
            shockMultiplier: 0
        );
    }

    public function test_cap_less_than_floor_throws(): void
    {
        $this->expectException(DomainException::class);

        new RiskModel(
            volatilityPercent: 0.1,
            compressionFactor: 1,
            shockMultiplier: 1,
            cap: 50,
            floor: 100
        );
    }

    public function test_hash_is_deterministic(): void
    {
        $model1 = new RiskModel(0.1, 1, 1.2, 100, 10);
        $model2 = new RiskModel(0.1, 1, 1.2, 100, 10);

        $this->assertEquals(
            $model1->hash(),
            $model2->hash()
        );
    }
}
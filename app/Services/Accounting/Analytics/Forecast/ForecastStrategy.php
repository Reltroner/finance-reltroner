<?php
// app/Services/Accounting/Analytics/Forecast/ForecastStrategy.php
namespace App\Services\Accounting\Analytics\Forecast;

final class ForecastStrategy
{
    public const LINEAR = 'linear';
    public const MOVING_AVERAGE = 'moving_average';
    public const CAGR = 'cagr';
    public const FIXED_GROWTH = 'fixed_growth';

    public static function supported(): array
    {
        return [
            self::LINEAR,
            self::MOVING_AVERAGE,
            self::CAGR,
            self::FIXED_GROWTH,
        ];
    }
}

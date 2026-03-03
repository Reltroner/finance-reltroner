<?php
// app/Support/Clock/SystemClock.php
namespace App\Support\Clock;

use DateTimeImmutable;

final class SystemClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
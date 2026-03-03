<?php
// app/Support/Clock/FixedClock.php
namespace App\Support\Clock;

use DateTimeImmutable;

final class FixedClock implements ClockInterface
{
    public function __construct(
        private readonly DateTimeImmutable $fixed
    ) {}

    public function now(): DateTimeImmutable
    {
        return $this->fixed;
    }
}
<?php
// app/Support/Clock/ClockInterface.php
namespace App\Support\Clock;

use DateTimeImmutable;

interface ClockInterface
{
    public function now(): DateTimeImmutable;
}
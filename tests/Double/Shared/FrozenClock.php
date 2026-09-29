<?php

declare(strict_types=1);

namespace App\Tests\Double\Shared;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final class FrozenClock implements ClockInterface
{
    private readonly DateTimeImmutable $now;

    public function __construct(string $now)
    {
        $this->now = new DateTimeImmutable($now);
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}

<?php

declare(strict_types=1);

namespace App\Person\Model\ValueObject;

use Symfony\Component\Uid\Uuid;

final readonly class PersonIdValueObject
{
    private function __construct(private Uuid $uuid)
    {
    }

    public static function generate(): self
    {
        return new self(Uuid::v6());
    }

    /**
     * @throws \InvalidArgumentException when the value is not a valid UUID
     */
    public static function fromString(string $value): self
    {
        return new self(Uuid::fromString($value));
    }

    public function toString(): string
    {
        return $this->uuid->toRfc4122();
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->uuid);
    }
}

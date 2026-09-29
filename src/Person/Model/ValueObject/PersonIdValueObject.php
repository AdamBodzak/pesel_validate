<?php

declare(strict_types=1);

namespace App\Person\Model\ValueObject;

use App\Person\Model\Exception\InvalidPersonIdException;
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
     * Accepts only the canonical RFC 4122 format (other formats supported by Uuid::fromString() are rejected).
     *
     * @throws InvalidPersonIdException
     */
    public static function fromString(string $value): self
    {
        if (!Uuid::isValid($value)) {
            throw InvalidPersonIdException::notRfc4122Uuid();
        }

        return new self(Uuid::fromString($value));
    }

    public function value(): string
    {
        return $this->uuid->toRfc4122();
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->uuid);
    }

    /**
     * Required by Doctrine: identifier objects are cast to string to build the identity map key.
     */
    public function __toString(): string
    {
        return $this->value();
    }
}

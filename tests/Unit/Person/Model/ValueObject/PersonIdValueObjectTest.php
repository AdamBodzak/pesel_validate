<?php

declare(strict_types=1);

namespace App\Tests\Unit\Person\Model\ValueObject;

use App\Person\Model\ValueObject\PersonIdValueObject;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PersonIdValueObjectTest extends TestCase
{
    public function testGeneratesUniqueIdentifiers(): void
    {
        self::assertFalse(PersonIdValueObject::generate()->equals(PersonIdValueObject::generate()));
    }

    public function testRestoresIdentifierFromString(): void
    {
        $id = PersonIdValueObject::generate();

        $restored = PersonIdValueObject::fromString($id->toString());

        self::assertTrue($restored->equals($id));
        self::assertSame($id->toString(), $restored->toString());
    }

    public function testRejectsInvalidUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PersonIdValueObject::fromString('not-a-uuid');
    }
}

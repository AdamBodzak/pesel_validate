<?php

declare(strict_types=1);

namespace App\Tests\Unit\Person\Model\ValueObject;

use App\Person\Model\Exception\InvalidPersonIdException;
use App\Person\Model\ValueObject\PersonIdValueObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class PersonIdValueObjectTest extends TestCase
{
    public function testGeneratesUniqueIdentifiers(): void
    {
        self::assertFalse(PersonIdValueObject::generate()->equals(PersonIdValueObject::generate()));
    }

    public function testRestoresIdentifierFromString(): void
    {
        $id = PersonIdValueObject::generate();

        $restored = PersonIdValueObject::fromString($id->value());

        self::assertTrue($restored->equals($id));
        self::assertSame($id->value(), $restored->value());
    }

    /**
     * @dataProvider invalidIdentifierProvider
     */
    public function testRejectsIdentifierNotInRfc4122Format(string $value): void
    {
        $this->expectException(InvalidPersonIdException::class);

        PersonIdValueObject::fromString($value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidIdentifierProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'random text' => ['not-a-uuid'];
        yield 'valid UUID in Base58 format' => [Uuid::v6()->toBase58()];
    }
}

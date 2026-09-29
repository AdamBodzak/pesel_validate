<?php

declare(strict_types=1);

namespace App\Tests\Unit\Person\Infrastructure\Doctrine\Type;

use App\Person\Infrastructure\Doctrine\Type\PersonIdDoctrineType;
use App\Person\Model\ValueObject\PersonIdValueObject;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use PHPUnit\Framework\TestCase;

final class PersonIdDoctrineTypeTest extends TestCase
{
    private PersonIdDoctrineType $type;
    private PostgreSQLPlatform $platform;

    protected function setUp(): void
    {
        $this->type = new PersonIdDoctrineType();
        $this->platform = new PostgreSQLPlatform();
    }

    public function testUsesNativeUuidColumn(): void
    {
        self::assertSame('UUID', $this->type->getSQLDeclaration([], $this->platform));
    }

    public function testConvertsValueObjectToDatabaseAndBack(): void
    {
        $id = PersonIdValueObject::generate();

        $databaseValue = $this->type->convertToDatabaseValue($id, $this->platform);
        $phpValue = $this->type->convertToPHPValue($databaseValue, $this->platform);

        self::assertSame($id->toString(), $databaseValue);
        self::assertInstanceOf(PersonIdValueObject::class, $phpValue);
        self::assertTrue($phpValue->equals($id));
    }

    public function testConvertsNull(): void
    {
        self::assertNull($this->type->convertToDatabaseValue(null, $this->platform));
        self::assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    public function testRejectsNonValueObjectForDatabase(): void
    {
        $this->expectException(ConversionException::class);

        $this->type->convertToDatabaseValue('1f0a1c2e-0000-6000-8000-000000000000', $this->platform);
    }

    public function testRejectsInvalidDatabaseValue(): void
    {
        $this->expectException(ConversionException::class);

        $this->type->convertToPHPValue('not-a-uuid', $this->platform);
    }
}

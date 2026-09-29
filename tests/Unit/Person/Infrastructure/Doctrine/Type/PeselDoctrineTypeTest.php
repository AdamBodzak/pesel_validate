<?php

declare(strict_types=1);

namespace App\Tests\Unit\Person\Infrastructure\Doctrine\Type;

use App\Person\Infrastructure\Doctrine\Type\PeselDoctrineType;
use App\Person\Model\ValueObject\PeselValueObject;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use PHPUnit\Framework\TestCase;

final class PeselDoctrineTypeTest extends TestCase
{
    private PeselDoctrineType $type;
    private PostgreSQLPlatform $platform;

    protected function setUp(): void
    {
        $this->type = new PeselDoctrineType();
        $this->platform = new PostgreSQLPlatform();
    }

    public function testConvertsValueObjectToDatabaseAndBack(): void
    {
        $pesel = PeselValueObject::fromString('44051401359');

        $databaseValue = $this->type->convertToDatabaseValue($pesel, $this->platform);
        $phpValue = $this->type->convertToPHPValue($databaseValue, $this->platform);

        self::assertSame('44051401359', $databaseValue);
        self::assertInstanceOf(PeselValueObject::class, $phpValue);
        self::assertTrue($phpValue->equals($pesel));
    }

    public function testConvertsNull(): void
    {
        self::assertNull($this->type->convertToDatabaseValue(null, $this->platform));
        self::assertNull($this->type->convertToPHPValue(null, $this->platform));
    }

    public function testConversionErrorDoesNotExposePesel(): void
    {
        try {
            $this->type->convertToPHPValue('44051401358', $this->platform);
            self::fail('Expected ConversionException was not thrown.');
        } catch (ConversionException $exception) {
            self::assertStringNotContainsString('44051401358', $exception->getMessage());
        }
    }

    public function testRejectsPlainStringForDatabaseWithoutExposingIt(): void
    {
        try {
            $this->type->convertToDatabaseValue('44051401359', $this->platform);
            self::fail('Expected ConversionException was not thrown.');
        } catch (ConversionException $exception) {
            self::assertStringNotContainsString('44051401359', $exception->getMessage());
        }
    }
}

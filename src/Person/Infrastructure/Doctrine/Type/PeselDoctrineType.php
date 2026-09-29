<?php

declare(strict_types=1);

namespace App\Person\Infrastructure\Doctrine\Type;

use App\Person\Model\Exception\InvalidPeselException;
use App\Person\Model\ValueObject\PeselValueObject;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\StringType;

/**
 * Conversion errors never contain the PESEL itself - it is personal data and must not end up in logs.
 */
final class PeselDoctrineType extends StringType
{
    public const NAME = 'pesel';

    private const REDACTED = '[redacted]';

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof PeselValueObject) {
            throw InvalidType::new(self::REDACTED, self::NAME, ['null', PeselValueObject::class]);
        }

        return $value->value();
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?PeselValueObject
    {
        if (null === $value || $value instanceof PeselValueObject) {
            return $value;
        }

        try {
            return PeselValueObject::fromString((string) $value);
        } catch (InvalidPeselException $exception) {
            throw ValueNotConvertible::new(self::REDACTED, self::NAME, null, $exception);
        }
    }
}

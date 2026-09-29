<?php

declare(strict_types=1);

namespace App\Person\Infrastructure\Doctrine\Type;

use App\Person\Model\Exception\InvalidPersonIdException;
use App\Person\Model\ValueObject\PersonIdValueObject;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\GuidType;

/**
 * Stored as native "uuid" column on PostgreSQL.
 */
final class PersonIdDoctrineType extends GuidType
{
    public const NAME = 'person_id';

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof PersonIdValueObject) {
            throw InvalidType::new($value, self::NAME, ['null', PersonIdValueObject::class]);
        }

        return $value->value();
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?PersonIdValueObject
    {
        if (null === $value || $value instanceof PersonIdValueObject) {
            return $value;
        }

        try {
            return PersonIdValueObject::fromString((string) $value);
        } catch (InvalidPersonIdException $exception) {
            throw ValueNotConvertible::new($value, self::NAME, null, $exception);
        }
    }
}

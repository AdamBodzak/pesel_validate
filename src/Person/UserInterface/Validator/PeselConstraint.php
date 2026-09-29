<?php

declare(strict_types=1);

namespace App\Person\UserInterface\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
final class PeselConstraint extends Constraint
{
    public const INVALID_FORMAT_ERROR = 'cc12423e-dac9-4032-a208-db566ec200de';
    public const INVALID_CHECKSUM_ERROR = '03c8b7a0-9557-4eed-a007-c7327c949f32';
    public const INVALID_BIRTH_DATE_ERROR = 'bf05ea76-e0f6-409d-b9cf-fd120ff1eb78';
    public const BIRTH_DATE_IN_FUTURE_ERROR = 'c3954d54-76d5-485c-aae7-2f6b43805b31';

    protected static $errorNames = [
        self::INVALID_FORMAT_ERROR => 'INVALID_FORMAT_ERROR',
        self::INVALID_CHECKSUM_ERROR => 'INVALID_CHECKSUM_ERROR',
        self::INVALID_BIRTH_DATE_ERROR => 'INVALID_BIRTH_DATE_ERROR',
        self::BIRTH_DATE_IN_FUTURE_ERROR => 'BIRTH_DATE_IN_FUTURE_ERROR',
    ];

    public string $invalidFormatMessage = 'person.pesel.invalid_format';
    public string $invalidChecksumMessage = 'person.pesel.invalid_checksum';
    public string $invalidBirthDateMessage = 'person.pesel.invalid_birth_date';
    public string $birthDateInFutureMessage = 'person.pesel.birth_date_in_future';
}

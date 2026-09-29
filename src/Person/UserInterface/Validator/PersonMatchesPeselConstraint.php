<?php

declare(strict_types=1);

namespace App\Person\UserInterface\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * Class-level constraint: birth date and gender must match the ones encoded in PESEL.
 * Violations are attached to the "birthDate" and "gender" fields.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class PersonMatchesPeselConstraint extends Constraint
{
    public const BIRTH_DATE_NOT_MATCHING_ERROR = 'd7b3a6f2-5c1e-4f0a-9b8d-2e6c4a1f3b57';
    public const GENDER_NOT_MATCHING_ERROR = '8e2f4c91-7a3d-4b6e-a5c0-1d9f7e3b2a64';

    protected static $errorNames = [
        self::BIRTH_DATE_NOT_MATCHING_ERROR => 'BIRTH_DATE_NOT_MATCHING_ERROR',
        self::GENDER_NOT_MATCHING_ERROR => 'GENDER_NOT_MATCHING_ERROR',
    ];

    public string $birthDateNotMatchingMessage = 'person.birth_date.not_matching_pesel';
    public string $genderNotMatchingMessage = 'person.gender.not_matching_pesel';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}

<?php

declare(strict_types=1);

namespace App\Person\Model\Exception;

use DomainException;

/**
 * Personal data is intentionally not part of the message - it must not end up in logs.
 */
final class PersonDataMismatchException extends DomainException
{
    public static function birthDateDoesNotMatchPesel(): self
    {
        return new self('Birth date does not match PESEL.');
    }

    public static function genderDoesNotMatchPesel(): self
    {
        return new self('Gender does not match PESEL.');
    }
}

<?php

declare(strict_types=1);

namespace App\Person\Model\Exception;

use DomainException;

final class InvalidPersonIdException extends DomainException
{
    public static function notRfc4122Uuid(): self
    {
        return new self('Person identifier must be a UUID in RFC 4122 format.');
    }
}

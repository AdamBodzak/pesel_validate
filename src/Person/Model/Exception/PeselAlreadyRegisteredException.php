<?php

declare(strict_types=1);

namespace App\Person\Model\Exception;

use DomainException;
use Throwable;

/**
 * The PESEL value is intentionally not part of the message - it is personal data and must not end up in logs.
 */
final class PeselAlreadyRegisteredException extends DomainException
{
    public static function create(?Throwable $previous = null): self
    {
        return new self('PESEL is already registered.', 0, $previous);
    }
}

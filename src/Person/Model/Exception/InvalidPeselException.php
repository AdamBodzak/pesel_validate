<?php

declare(strict_types=1);

namespace App\Person\Model\Exception;

use App\Person\Model\Enum\PeselErrorEnum;
use DomainException;

/**
 * The PESEL value is intentionally not part of the message - it is personal data and must not end up in logs.
 */
final class InvalidPeselException extends DomainException
{
    private function __construct(private readonly PeselErrorEnum $error)
    {
        parent::__construct(sprintf('Invalid PESEL: %s.', $error->value));
    }

    public static function invalidFormat(): self
    {
        return new self(PeselErrorEnum::InvalidFormat);
    }

    public static function invalidChecksum(): self
    {
        return new self(PeselErrorEnum::InvalidChecksum);
    }

    public static function invalidBirthDate(): self
    {
        return new self(PeselErrorEnum::InvalidBirthDate);
    }

    public function error(): PeselErrorEnum
    {
        return $this->error;
    }
}

<?php

declare(strict_types=1);

namespace App\Person\Model\Enum;

enum PeselErrorEnum: string
{
    case InvalidFormat = 'invalid_format';
    case InvalidChecksum = 'invalid_checksum';
    case InvalidBirthDate = 'invalid_birth_date';
}

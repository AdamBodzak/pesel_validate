<?php

declare(strict_types=1);

namespace App\Person\Application\RegisterPerson;

use App\Person\Model\Enum\GenderEnum;
use DateTimeImmutable;

final readonly class RegisterPersonCommand
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $pesel,
        public DateTimeImmutable $birthDate,
        public GenderEnum $gender,
    ) {
    }
}

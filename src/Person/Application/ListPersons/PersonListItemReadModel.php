<?php

declare(strict_types=1);

namespace App\Person\Application\ListPersons;

use App\Person\Model\Enum\GenderEnum;
use DateTimeImmutable;

/**
 * Raw read-side data. Contains the full PESEL - it must be masked before it reaches any view.
 */
final readonly class PersonListItemReadModel
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
        public string $pesel,
        public DateTimeImmutable $birthDate,
        public GenderEnum $gender,
        public DateTimeImmutable $createdAt,
    ) {
    }
}

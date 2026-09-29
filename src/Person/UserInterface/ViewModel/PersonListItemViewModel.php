<?php

declare(strict_types=1);

namespace App\Person\UserInterface\ViewModel;

use App\Person\Application\ListPersons\PersonListItemReadModel;
use DateTimeImmutable;

/**
 * Data ready for the person list template. The full PESEL never gets here - only its masked form.
 */
final readonly class PersonListItemViewModel
{
    private const PESEL_VISIBLE_DIGITS = 6;
    private const PESEL_MASK = '*****';

    private function __construct(
        public string $firstName,
        public string $lastName,
        public string $maskedPesel,
        public DateTimeImmutable $birthDate,
        public string $genderLabel,
        public DateTimeImmutable $createdAt,
    ) {
    }

    public static function fromReadModel(PersonListItemReadModel $person): self
    {
        return new self(
            $person->firstName,
            $person->lastName,
            substr($person->pesel, 0, self::PESEL_VISIBLE_DIGITS).self::PESEL_MASK,
            $person->birthDate,
            'person.gender.'.$person->gender->value,
            $person->createdAt,
        );
    }
}

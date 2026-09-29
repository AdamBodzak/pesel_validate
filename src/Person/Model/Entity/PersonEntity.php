<?php

declare(strict_types=1);

namespace App\Person\Model\Entity;

use App\Person\Model\Enum\GenderEnum;
use App\Person\Model\Exception\PersonDataMismatchException;
use App\Person\Model\ValueObject\PersonIdValueObject;
use App\Person\Model\ValueObject\PeselValueObject;
use DateTimeImmutable;

/**
 * Not final - Doctrine creates lazy-loading proxies by extending entity classes.
 */
class PersonEntity
{
    private function __construct(
        private PersonIdValueObject $id,
        private string $firstName,
        private string $lastName,
        private PeselValueObject $pesel,
        private DateTimeImmutable $birthDate,
        private GenderEnum $gender,
        private DateTimeImmutable $createdAt,
    ) {
    }

    /**
     * @throws PersonDataMismatchException when birth date or gender does not match the PESEL
     */
    public static function register(
        PersonIdValueObject $id,
        string $firstName,
        string $lastName,
        PeselValueObject $pesel,
        DateTimeImmutable $birthDate,
        GenderEnum $gender,
        DateTimeImmutable $createdAt,
    ): self {
        if (!$pesel->matchesBirthDate($birthDate)) {
            throw PersonDataMismatchException::birthDateDoesNotMatchPesel();
        }

        if (!$pesel->matchesGender($gender)) {
            throw PersonDataMismatchException::genderDoesNotMatchPesel();
        }

        return new self($id, $firstName, $lastName, $pesel, $birthDate, $gender, $createdAt);
    }

    public function id(): PersonIdValueObject
    {
        return $this->id;
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function lastName(): string
    {
        return $this->lastName;
    }

    public function pesel(): PeselValueObject
    {
        return $this->pesel;
    }

    public function birthDate(): DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function gender(): GenderEnum
    {
        return $this->gender;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}

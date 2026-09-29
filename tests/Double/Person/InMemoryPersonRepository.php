<?php

declare(strict_types=1);

namespace App\Tests\Double\Person;

use App\Person\Model\Entity\PersonEntity;
use App\Person\Model\Exception\PeselAlreadyRegisteredException;
use App\Person\Model\Repository\PersonRepositoryInterface;
use App\Person\Model\ValueObject\PeselValueObject;

final class InMemoryPersonRepository implements PersonRepositoryInterface
{
    /** @var array<string, PersonEntity> indexed by PESEL, mirrors the unique index in the database */
    private array $persons = [];

    public function save(PersonEntity $person): void
    {
        if ($this->existsByPesel($person->pesel())) {
            throw PeselAlreadyRegisteredException::create();
        }

        $this->persons[$person->pesel()->value()] = $person;
    }

    public function existsByPesel(PeselValueObject $pesel): bool
    {
        return isset($this->persons[$pesel->value()]);
    }

    /**
     * @return list<PersonEntity>
     */
    public function all(): array
    {
        return array_values($this->persons);
    }
}

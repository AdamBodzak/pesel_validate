<?php

declare(strict_types=1);

namespace App\Person\Model\Repository;

use App\Person\Model\Entity\PersonEntity;
use App\Person\Model\Exception\PeselAlreadyRegisteredException;
use App\Person\Model\ValueObject\PeselValueObject;

interface PersonRepositoryInterface
{
    /**
     * @throws PeselAlreadyRegisteredException when a person with the same PESEL was saved in the meantime
     */
    public function save(PersonEntity $person): void;

    public function existsByPesel(PeselValueObject $pesel): bool;
}

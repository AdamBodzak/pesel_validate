<?php

declare(strict_types=1);

namespace App\Person\Application\RegisterPerson;

use App\Person\Model\Entity\PersonEntity;
use App\Person\Model\Exception\InvalidPeselException;
use App\Person\Model\Exception\PersonDataMismatchException;
use App\Person\Model\Exception\PeselAlreadyRegisteredException;
use App\Person\Model\Repository\PersonRepositoryInterface;
use App\Person\Model\ValueObject\PersonIdValueObject;
use App\Person\Model\ValueObject\PeselValueObject;
use Psr\Clock\ClockInterface;

final class RegisterPersonHandler
{
    public function __construct(
        private readonly PersonRepositoryInterface $personRepository,
        private readonly ClockInterface $clock,
    ) {
    }

    /**
     * @throws InvalidPeselException
     * @throws PersonDataMismatchException
     * @throws PeselAlreadyRegisteredException
     */
    public function handle(RegisterPersonCommand $command): void
    {
        $pesel = PeselValueObject::fromString($command->pesel);

        if ($this->personRepository->existsByPesel($pesel)) {
            throw PeselAlreadyRegisteredException::create();
        }

        $person = PersonEntity::register(
            PersonIdValueObject::generate(),
            $command->firstName,
            $command->lastName,
            $pesel,
            $command->birthDate,
            $command->gender,
            $this->clock->now(),
        );

        $this->personRepository->save($person);
    }
}

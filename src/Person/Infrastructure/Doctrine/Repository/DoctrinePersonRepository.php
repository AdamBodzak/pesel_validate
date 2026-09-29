<?php

declare(strict_types=1);

namespace App\Person\Infrastructure\Doctrine\Repository;

use App\Person\Model\Entity\PersonEntity;
use App\Person\Model\Exception\PeselAlreadyRegisteredException;
use App\Person\Model\Repository\PersonRepositoryInterface;
use App\Person\Model\ValueObject\PeselValueObject;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePersonRepository implements PersonRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function save(PersonEntity $person): void
    {
        $this->entityManager->persist($person);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            // Race condition: another request registered the same PESEL after the existsByPesel() check.
            // PESEL is the only unique column besides the generated UUID primary key.
            throw PeselAlreadyRegisteredException::create($exception);
        }
    }

    public function existsByPesel(PeselValueObject $pesel): bool
    {
        return $this->entityManager->getRepository(PersonEntity::class)->count(['pesel' => $pesel]) > 0;
    }
}

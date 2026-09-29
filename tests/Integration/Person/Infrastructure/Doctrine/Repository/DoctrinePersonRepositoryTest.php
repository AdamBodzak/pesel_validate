<?php

declare(strict_types=1);

namespace App\Tests\Integration\Person\Infrastructure\Doctrine\Repository;

use App\Person\Infrastructure\Doctrine\Repository\DoctrinePersonRepository;
use App\Person\Model\Entity\PersonEntity;
use App\Person\Model\Enum\GenderEnum;
use App\Person\Model\Exception\PeselAlreadyRegisteredException;
use App\Person\Model\ValueObject\PersonIdValueObject;
use App\Person\Model\ValueObject\PeselValueObject;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Runs against the real test database; every test is rolled back by DAMA\DoctrineTestBundle.
 */
final class DoctrinePersonRepositoryTest extends KernelTestCase
{
    private const PESEL = '44051401359';

    private EntityManagerInterface $entityManager;
    private DoctrinePersonRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $entityManager = self::getContainer()->get('doctrine')->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $this->entityManager = $entityManager;
        $this->repository = new DoctrinePersonRepository($entityManager);
    }

    public function testSavesAndRestoresPerson(): void
    {
        $person = $this->person(self::PESEL);

        $this->repository->save($person);
        $this->entityManager->clear();

        $restored = $this->entityManager->find(PersonEntity::class, $person->id());

        self::assertInstanceOf(PersonEntity::class, $restored);
        self::assertTrue($restored->id()->equals($person->id()));
        self::assertSame('Jan', $restored->firstName());
        self::assertSame('Kowalski', $restored->lastName());
        self::assertTrue($restored->pesel()->equals($person->pesel()));
        self::assertSame('1944-05-14', $restored->birthDate()->format('Y-m-d'));
        self::assertSame(GenderEnum::Male, $restored->gender());
        self::assertSame('2026-09-29 12:00:00', $restored->createdAt()->format('Y-m-d H:i:s'));
    }

    public function testChecksWhetherPeselExists(): void
    {
        $this->repository->save($this->person(self::PESEL));

        self::assertTrue($this->repository->existsByPesel(PeselValueObject::fromString(self::PESEL)));
        self::assertFalse($this->repository->existsByPesel(PeselValueObject::fromString('85831512348')));
    }

    public function testTranslatesUniqueIndexViolationIntoDomainException(): void
    {
        $this->repository->save($this->person(self::PESEL));

        $this->expectException(PeselAlreadyRegisteredException::class);

        // Simulates a race condition: the existsByPesel() check is bypassed, the unique index must catch it
        $this->repository->save($this->person(self::PESEL));
    }

    private function person(string $pesel): PersonEntity
    {
        return PersonEntity::register(
            PersonIdValueObject::generate(),
            'Jan',
            'Kowalski',
            PeselValueObject::fromString($pesel),
            new DateTimeImmutable('1944-05-14'),
            GenderEnum::Male,
            new DateTimeImmutable('2026-09-29 12:00:00'),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Person\Infrastructure\Doctrine\Query;

use App\Person\Infrastructure\Doctrine\Query\DbalPersonListQuery;
use App\Person\Infrastructure\Doctrine\Repository\DoctrinePersonRepository;
use App\Person\Model\Entity\PersonEntity;
use App\Person\Model\Enum\GenderEnum;
use App\Person\Model\ValueObject\PersonIdValueObject;
use App\Person\Model\ValueObject\PeselValueObject;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Runs against the real test database; every test is rolled back by DAMA\DoctrineTestBundle.
 */
final class DbalPersonListQueryTest extends KernelTestCase
{
    private DoctrinePersonRepository $repository;
    private DbalPersonListQuery $query;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $this->repository = new DoctrinePersonRepository($entityManager);
        $this->query = new DbalPersonListQuery($entityManager->getConnection());
    }

    public function testReturnsEmptyListWhenNobodyIsRegistered(): void
    {
        self::assertSame([], $this->query->findAllNewestFirst());
    }

    public function testReturnsPersonsNewestFirstWithAllFields(): void
    {
        $older = $this->register('Jan', '44051401359', '1944-05-14', GenderEnum::Male, '2026-09-29 10:00:00');
        $newer = $this->register('Anna', '85831512348', '1885-03-15', GenderEnum::Female, '2026-09-29 11:00:00');

        $persons = $this->query->findAllNewestFirst();

        self::assertCount(2, $persons);
        self::assertSame([$newer->id()->value(), $older->id()->value()], [$persons[0]->id, $persons[1]->id]);

        $person = $persons[0];
        self::assertSame('Anna', $person->firstName);
        self::assertSame('Kowalski', $person->lastName);
        self::assertSame('85831512348', $person->pesel);
        self::assertSame('1885-03-15', $person->birthDate->format('Y-m-d'));
        self::assertSame(GenderEnum::Female, $person->gender);
        self::assertSame('2026-09-29 11:00:00', $person->createdAt->format('Y-m-d H:i:s'));
    }

    private function register(
        string $firstName,
        string $pesel,
        string $birthDate,
        GenderEnum $gender,
        string $createdAt,
    ): PersonEntity {
        $person = PersonEntity::register(
            PersonIdValueObject::generate(),
            $firstName,
            'Kowalski',
            PeselValueObject::fromString($pesel),
            new DateTimeImmutable($birthDate),
            $gender,
            new DateTimeImmutable($createdAt),
        );
        $this->repository->save($person);

        return $person;
    }
}

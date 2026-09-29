<?php

declare(strict_types=1);

namespace App\Person\Infrastructure\Doctrine\Query;

use App\Person\Application\ListPersons\PersonListItemReadModel;
use App\Person\Application\ListPersons\PersonListQueryInterface;
use App\Person\Model\Enum\GenderEnum;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;

/**
 * Read side: plain SQL straight into read models, without hydrating entities.
 */
final class DbalPersonListQuery implements PersonListQueryInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function findAllNewestFirst(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, first_name, last_name, pesel, birth_date, gender, created_at
             FROM person
             ORDER BY created_at DESC, id DESC',
        );

        return array_map(
            static fn (array $row): PersonListItemReadModel => new PersonListItemReadModel(
                (string) $row['id'],
                (string) $row['first_name'],
                (string) $row['last_name'],
                (string) $row['pesel'],
                new DateTimeImmutable((string) $row['birth_date']),
                GenderEnum::from((string) $row['gender']),
                new DateTimeImmutable((string) $row['created_at']),
            ),
            $rows,
        );
    }
}

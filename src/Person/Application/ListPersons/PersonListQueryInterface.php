<?php

declare(strict_types=1);

namespace App\Person\Application\ListPersons;

interface PersonListQueryInterface
{
    /**
     * @return list<PersonListItemReadModel> the most recently registered first
     */
    public function findAllNewestFirst(): array;
}

<?php

declare(strict_types=1);

namespace App\Person\UserInterface\Http\Controller;

use App\Person\Application\ListPersons\PersonListQueryInterface;
use App\Person\UserInterface\Http\Response\PersonListResponseFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route('/persons', name: 'person_list', methods: ['GET'])]
final class PersonListController
{
    public function __construct(
        private readonly PersonListQueryInterface $personListQuery,
        private readonly PersonListResponseFactory $responseFactory,
    ) {
    }

    public function __invoke(): Response
    {
        return $this->responseFactory->list($this->personListQuery->findAllNewestFirst());
    }
}

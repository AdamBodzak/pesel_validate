<?php

declare(strict_types=1);

namespace App\Person\UserInterface\Http\Response;

use App\Person\Application\ListPersons\PersonListItemReadModel;
use App\Person\UserInterface\ViewModel\PersonListItemViewModel;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class PersonListResponseFactory
{
    public function __construct(private readonly Environment $twig)
    {
    }

    /**
     * @param list<PersonListItemReadModel> $persons
     */
    public function list(array $persons): Response
    {
        return new Response($this->twig->render('person/list.html.twig', [
            'persons' => array_map(PersonListItemViewModel::fromReadModel(...), $persons),
        ]));
    }
}

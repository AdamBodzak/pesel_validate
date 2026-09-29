<?php

declare(strict_types=1);

namespace App\Tests\Functional\Person\UserInterface\Http\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PersonListControllerTest extends WebTestCase
{
    public function testDisplaysEmptyList(): void
    {
        $client = self::createClient();

        $client->request('GET', '/persons');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Zarejestrowane osoby');
        self::assertSelectorTextContains('main', 'Nie zarejestrowano jeszcze żadnej osoby.');
        self::assertSelectorNotExists('table');
    }

    public function testRejectsPostMethod(): void
    {
        $client = self::createClient();

        $client->request('POST', '/persons');

        self::assertResponseStatusCodeSame(405);
    }
}

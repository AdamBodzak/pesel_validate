<?php

declare(strict_types=1);

namespace App\Tests\Functional\Person\UserInterface\Http\Controller;

use JsonException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class DecodePeselControllerTest extends WebTestCase
{
    private const URL = '/pesel/decode';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testDecodesBirthDateAndGenderWithoutEchoingPesel(): void
    {
        $this->decode(json_encode(['pesel' => '440 514 013 59'], JSON_THROW_ON_ERROR), $this->csrfToken());

        self::assertResponseIsSuccessful();
        self::assertSame([
            'birthDate' => '1944-05-14',
            'gender' => 'male',
            'hint' => 'Z numeru PESEL: 14.05.1944, mężczyzna',
        ], $this->responseJson());
        self::assertStringNotContainsString('44051401359', (string) $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider invalidPeselProvider
     */
    public function testReturnsValidationMessageForInvalidPesel(string $pesel, string $expectedMessage): void
    {
        $this->decode(json_encode(['pesel' => $pesel], JSON_THROW_ON_ERROR), $this->csrfToken());

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(['message' => $expectedMessage], $this->responseJson());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidPeselProvider(): iterable
    {
        yield 'empty' => ['  ', 'Podaj numer PESEL.'];
        yield 'invalid format' => ['4405140135a', 'Numer PESEL musi składać się z 11 cyfr.'];
        yield 'invalid checksum' => ['44051401358', 'Numer PESEL ma nieprawidłową cyfrę kontrolną.'];
        yield 'birth date in the future' => ['05470145138', 'Numer PESEL wskazuje datę urodzenia z przyszłości.'];
    }

    public function testDoesNotRevealWhetherPeselIsRegistered(): void
    {
        $crawler = $this->client->request('GET', '/');
        $form = $crawler->filter('form[name="register_person_form"]')->form([
            'register_person_form[firstName]' => 'Jan',
            'register_person_form[lastName]' => 'Kowalski',
            'register_person_form[pesel]' => '44051401359',
            'register_person_form[birthDate]' => '1944-05-14',
            'register_person_form[gender]' => 'male',
        ]);
        $this->client->submit($form);
        self::assertResponseRedirects('/persons');

        $this->decode(json_encode(['pesel' => '44051401359'], JSON_THROW_ON_ERROR), $this->csrfToken());

        self::assertResponseIsSuccessful();
        self::assertSame('1944-05-14', $this->responseJson()['birthDate']);
    }

    /**
     * @dataProvider invalidCsrfTokenProvider
     */
    public function testRejectsRequestWithoutValidCsrfToken(?string $token): void
    {
        $this->decode(json_encode(['pesel' => '44051401359'], JSON_THROW_ON_ERROR), $token);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertArrayHasKey('message', $this->responseJson());
    }

    /**
     * @return iterable<string, array{?string}>
     */
    public static function invalidCsrfTokenProvider(): iterable
    {
        yield 'missing token' => [null];
        yield 'invalid token' => ['invalid'];
    }

    /**
     * @dataProvider invalidRequestProvider
     */
    public function testRejectsInvalidRequestBody(string $body, string $contentType): void
    {
        $this->decode($body, $this->csrfToken(), $contentType);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(['message' => 'Nieprawidłowe zapytanie.'], $this->responseJson());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidRequestProvider(): iterable
    {
        yield 'not JSON content type' => ['pesel=44051401359', 'application/x-www-form-urlencoded'];
        yield 'malformed JSON' => ['{"pesel":', 'application/json'];
        yield 'missing pesel key' => ['{"number":"44051401359"}', 'application/json'];
        yield 'pesel is not a string' => ['{"pesel":44051401359}', 'application/json'];
    }

    public function testRejectsGetMethod(): void
    {
        $this->client->request('GET', self::URL);

        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    private function decode(string $body, ?string $csrfToken, string $contentType = 'application/json'): void
    {
        $server = ['CONTENT_TYPE' => $contentType, 'HTTP_ACCEPT' => 'application/json'];
        if (null !== $csrfToken) {
            $server['HTTP_X_CSRF_TOKEN'] = $csrfToken;
        }

        $this->client->request('POST', self::URL, [], [], $server, $body);
    }

    /**
     * Reads the token the same way the browser does - from the Alpine.js component config on the registration page.
     */
    private function csrfToken(): string
    {
        $crawler = $this->client->request('GET', '/');
        $componentConfig = (string) $crawler->filter('form[name="register_person_form"]')->attr('x-data');

        self::assertSame(1, preg_match('/^registerPersonForm\((.*)\)$/s', $componentConfig, $matches));

        return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR)['csrfToken'];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    private function responseJson(): array
    {
        return json_decode((string) $this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}

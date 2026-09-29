<?php

declare(strict_types=1);

namespace App\Tests\Functional\Person\UserInterface\Http\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;

/**
 * Full HTTP flow of the registration form; the database is rolled back after every test (DAMA\DoctrineTestBundle).
 */
final class RegisterPersonControllerTest extends WebTestCase
{
    private const FORM_NAME = 'register_person_form';
    private const VALID_DATA = [
        'firstName' => 'Jan',
        'lastName' => 'Kowalski',
        'pesel' => '44051401359',
        'birthDate' => '1944-05-14',
        'gender' => 'male',
    ];

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testDisplaysRegistrationForm(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Rejestracja osoby');
        foreach (['firstName', 'lastName', 'pesel', 'birthDate'] as $field) {
            self::assertSelectorExists(sprintf('input[name="%s[%s]"]', self::FORM_NAME, $field));
        }
        self::assertSelectorExists(sprintf('input[type="radio"][name="%s[gender]"][value="female"]', self::FORM_NAME));
        self::assertSelectorExists(sprintf('input[type="radio"][name="%s[gender]"][value="male"]', self::FORM_NAME));
    }

    public function testRegistersPersonAndShowsItOnTheListWithMaskedPesel(): void
    {
        // Spaces are removed by the PESEL input normalizer
        $this->submit(['pesel' => '440 514 013 59']);

        self::assertResponseRedirects('/persons');

        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('[role="alert"]', 'Osoba została zarejestrowana.');
        self::assertSelectorTextContains('table', 'Kowalski');
        self::assertSelectorTextContains('table', '440514*****');
        self::assertStringNotContainsString('44051401359', (string) $this->client->getResponse()->getContent());
    }

    public function testRejectsEmptyForm(): void
    {
        $this->submit(['firstName' => '', 'lastName' => '', 'pesel' => '', 'birthDate' => '', 'gender' => null]);

        $this->assertRejectedWith(
            'Podaj imię.',
            'Podaj nazwisko.',
            'Podaj numer PESEL.',
            'Podaj datę urodzenia.',
            'Wybierz płeć.',
        );
    }

    public function testRejectsInvalidPesel(): void
    {
        $this->submit(['pesel' => '44051401358']);

        $this->assertRejectedWith('Numer PESEL ma nieprawidłową cyfrę kontrolną.');
    }

    public function testRejectsBirthDateAndGenderNotMatchingPesel(): void
    {
        $this->submit(['birthDate' => '1944-05-15', 'gender' => 'female']);

        $this->assertRejectedWith(
            'Data urodzenia nie zgadza się z numerem PESEL.',
            'Płeć nie zgadza się z numerem PESEL.',
        );
    }

    public function testRejectsInvalidName(): void
    {
        $this->submit(['firstName' => 'J4n']);

        $this->assertRejectedWith('Imię może zawierać tylko litery');
    }

    public function testRejectsAlreadyRegisteredPesel(): void
    {
        $this->submit();
        self::assertResponseRedirects('/persons');

        $this->submit(['firstName' => 'Adam']);

        $this->assertRejectedWith('Osoba o tym numerze PESEL jest już zarejestrowana.');
    }

    public function testRejectsInvalidCsrfTokenAndDoesNotRegisterPerson(): void
    {
        $this->submit(['_token' => 'invalid']);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->client->request('GET', '/persons');
        self::assertSelectorTextContains('main', 'Nie zarejestrowano jeszcze żadnej osoby.');
    }

    /**
     * @param array<string, string|null> $overrides null leaves the field untouched (e.g. no radio selected)
     */
    private function submit(array $overrides = []): void
    {
        $crawler = $this->client->request('GET', '/');
        $form = $crawler->filter(sprintf('form[name="%s"]', self::FORM_NAME))->form();

        foreach (array_merge(self::VALID_DATA, $overrides) as $field => $value) {
            if (null !== $value) {
                $this->field($form, $field)->setValue($value);
            }
        }

        $this->client->submit($form);
    }

    private function field(Form $form, string $field): FormField
    {
        $formField = $form->get(sprintf('%s[%s]', self::FORM_NAME, $field));
        self::assertInstanceOf(FormField::class, $formField);

        return $formField;
    }

    private function assertRejectedWith(string ...$messages): void
    {
        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $content = (string) $this->client->getResponse()->getContent();
        foreach ($messages as $message) {
            self::assertStringContainsString(htmlspecialchars($message, ENT_QUOTES), $content);
        }
    }
}

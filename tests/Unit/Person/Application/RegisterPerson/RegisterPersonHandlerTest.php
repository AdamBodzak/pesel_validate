<?php

declare(strict_types=1);

namespace App\Tests\Unit\Person\Application\RegisterPerson;

use App\Person\Application\RegisterPerson\RegisterPersonCommand;
use App\Person\Application\RegisterPerson\RegisterPersonHandler;
use App\Person\Model\Enum\GenderEnum;
use App\Person\Model\Exception\InvalidPeselException;
use App\Person\Model\Exception\PersonDataMismatchException;
use App\Person\Model\Exception\PeselAlreadyRegisteredException;
use App\Tests\Double\Person\InMemoryPersonRepository;
use App\Tests\Double\Shared\FrozenClock;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class RegisterPersonHandlerTest extends TestCase
{
    private const NOW = '2026-09-29 12:00:00';

    private InMemoryPersonRepository $personRepository;
    private RegisterPersonHandler $handler;

    protected function setUp(): void
    {
        $this->personRepository = new InMemoryPersonRepository();
        $this->handler = new RegisterPersonHandler($this->personRepository, new FrozenClock(self::NOW));
    }

    public function testRegistersPerson(): void
    {
        $this->handler->handle($this->command());

        $persons = $this->personRepository->all();
        self::assertCount(1, $persons);

        $person = $persons[0];
        self::assertSame('Jan', $person->firstName());
        self::assertSame('Kowalski', $person->lastName());
        self::assertSame('44051401359', $person->pesel()->value());
        self::assertSame('1944-05-14', $person->birthDate()->format('Y-m-d'));
        self::assertSame(GenderEnum::Male, $person->gender());
        self::assertSame(self::NOW, $person->createdAt()->format('Y-m-d H:i:s'));
    }

    public function testRejectsAlreadyRegisteredPesel(): void
    {
        $this->handler->handle($this->command());

        try {
            $this->handler->handle($this->command(firstName: 'Adam'));
            self::fail('Expected PeselAlreadyRegisteredException was not thrown.');
        } catch (PeselAlreadyRegisteredException) {
            self::assertCount(1, $this->personRepository->all());
        }
    }

    public function testRejectsInvalidPesel(): void
    {
        $this->expectException(InvalidPeselException::class);

        $this->handler->handle($this->command(pesel: '44051401358'));
    }

    public function testRejectsDataNotMatchingPesel(): void
    {
        $this->expectException(PersonDataMismatchException::class);

        $this->handler->handle($this->command(gender: GenderEnum::Female));
    }

    private function command(
        string $firstName = 'Jan',
        string $pesel = '44051401359',
        GenderEnum $gender = GenderEnum::Male,
    ): RegisterPersonCommand {
        return new RegisterPersonCommand(
            $firstName,
            'Kowalski',
            $pesel,
            new DateTimeImmutable('1944-05-14'),
            $gender,
        );
    }
}

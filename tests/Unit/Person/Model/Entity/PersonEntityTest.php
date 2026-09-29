<?php

declare(strict_types=1);

namespace App\Tests\Unit\Person\Model\Entity;

use App\Person\Model\Entity\PersonEntity;
use App\Person\Model\Enum\GenderEnum;
use App\Person\Model\Exception\PersonDataMismatchException;
use App\Person\Model\ValueObject\PersonIdValueObject;
use App\Person\Model\ValueObject\PeselValueObject;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class PersonEntityTest extends TestCase
{
    private const PESEL = '44051401359';

    public function testRegistersPersonWithDataMatchingPesel(): void
    {
        $id = PersonIdValueObject::generate();
        $pesel = PeselValueObject::fromString(self::PESEL);
        $createdAt = new DateTimeImmutable('2026-09-29 12:00:00');

        $person = PersonEntity::register(
            $id,
            'Jan',
            'Kowalski',
            $pesel,
            new DateTimeImmutable('1944-05-14'),
            GenderEnum::Male,
            $createdAt,
        );

        self::assertTrue($person->id()->equals($id));
        self::assertSame('Jan', $person->firstName());
        self::assertSame('Kowalski', $person->lastName());
        self::assertTrue($person->pesel()->equals($pesel));
        self::assertSame('1944-05-14', $person->birthDate()->format('Y-m-d'));
        self::assertSame(GenderEnum::Male, $person->gender());
        self::assertSame($createdAt, $person->createdAt());
    }

    public function testIgnoresTimeOfBirthDate(): void
    {
        $person = $this->register(new DateTimeImmutable('1944-05-14 23:59:59'), GenderEnum::Male);

        self::assertSame('1944-05-14', $person->birthDate()->format('Y-m-d'));
    }

    public function testRejectsBirthDateNotMatchingPesel(): void
    {
        $this->expectException(PersonDataMismatchException::class);
        $this->expectExceptionMessage('Birth date does not match PESEL.');

        $this->register(new DateTimeImmutable('1944-05-15'), GenderEnum::Male);
    }

    public function testRejectsGenderNotMatchingPesel(): void
    {
        $this->expectException(PersonDataMismatchException::class);
        $this->expectExceptionMessage('Gender does not match PESEL.');

        $this->register(new DateTimeImmutable('1944-05-14'), GenderEnum::Female);
    }

    private function register(DateTimeImmutable $birthDate, GenderEnum $gender): PersonEntity
    {
        return PersonEntity::register(
            PersonIdValueObject::generate(),
            'Jan',
            'Kowalski',
            PeselValueObject::fromString(self::PESEL),
            $birthDate,
            $gender,
            new DateTimeImmutable('2026-09-29 12:00:00'),
        );
    }
}

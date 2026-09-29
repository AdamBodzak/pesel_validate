<?php

declare(strict_types=1);

namespace App\Tests\Unit\Person\Model\ValueObject;

use App\Person\Model\Enum\GenderEnum;
use App\Person\Model\Enum\PeselErrorEnum;
use App\Person\Model\Exception\InvalidPeselException;
use App\Person\Model\ValueObject\PeselValueObject;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

final class PeselValueObjectTest extends TestCase
{
    /**
     * @dataProvider validPeselProvider
     */
    public function testCreatesPeselAndDecodesBirthDateAndGender(
        string $value,
        string $expectedBirthDate,
        GenderEnum $expectedGender,
    ): void {
        $pesel = PeselValueObject::fromString($value);

        self::assertSame($value, $pesel->value());
        self::assertSame($expectedBirthDate, $pesel->birthDate()->format('Y-m-d'));
        self::assertSame($expectedGender, $pesel->gender());
    }

    /**
     * @return iterable<string, array{string, string, GenderEnum}>
     */
    public static function validPeselProvider(): iterable
    {
        yield '1800s (month + 80)' => ['85831512348', '1885-03-15', GenderEnum::Female];
        yield '1900s (month + 0)' => ['44051401359', '1944-05-14', GenderEnum::Male];
        yield '1900s, last day of the century' => ['99123156714', '1999-12-31', GenderEnum::Male];
        yield '2000s (month + 20), leap day in 2000' => ['00222900245', '2000-02-29', GenderEnum::Female];
        yield '2100s (month + 40)' => ['05470145138', '2105-07-01', GenderEnum::Male];
        yield '2200s (month + 60)' => ['99723100001', '2299-12-31', GenderEnum::Female];
    }

    /**
     * @dataProvider whitespaceProvider
     */
    public function testRemovesWhitespaceBeforeValidation(string $value): void
    {
        self::assertSame('44051401359', PeselValueObject::fromString($value)->value());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function whitespaceProvider(): iterable
    {
        yield 'leading and trailing spaces' => ['  44051401359  '];
        yield 'spaces between digits' => ['440 514 013 59'];
        yield 'tab and new line' => ["\t44051401359\n"];
        yield 'non-breaking space' => ["440514\u{00A0}01359"];
    }

    /**
     * @dataProvider invalidPeselProvider
     */
    public function testRejectsInvalidPesel(string $value, PeselErrorEnum $expectedError): void
    {
        try {
            PeselValueObject::fromString($value);
            self::fail('Expected InvalidPeselException was not thrown.');
        } catch (InvalidPeselException $exception) {
            self::assertSame($expectedError, $exception->error());
        }
    }

    /**
     * @return iterable<string, array{string, PeselErrorEnum}>
     */
    public static function invalidPeselProvider(): iterable
    {
        yield 'empty string' => ['', PeselErrorEnum::InvalidFormat];
        yield 'whitespace only' => ['   ', PeselErrorEnum::InvalidFormat];
        yield 'too short' => ['4405140135', PeselErrorEnum::InvalidFormat];
        yield 'too long' => ['440514013590', PeselErrorEnum::InvalidFormat];
        yield 'contains a letter' => ['4405140135a', PeselErrorEnum::InvalidFormat];
        yield 'contains a dash' => ['440514-01359', PeselErrorEnum::InvalidFormat];
        yield 'non-ASCII digits' => ['４４０５１４０１３５９', PeselErrorEnum::InvalidFormat];
        yield 'invalid UTF-8' => ["4405140135\xFF", PeselErrorEnum::InvalidFormat];

        yield 'wrong checksum digit' => ['44051401358', PeselErrorEnum::InvalidChecksum];
        yield 'swapped digits' => ['44051401395', PeselErrorEnum::InvalidChecksum];
        yield 'checksum is checked before date' => ['90131012340', PeselErrorEnum::InvalidChecksum];

        yield 'month 00' => ['90001012340', PeselErrorEnum::InvalidBirthDate];
        yield 'month 13' => ['90131012346', PeselErrorEnum::InvalidBirthDate];
        yield 'day 00' => ['90010012342', PeselErrorEnum::InvalidBirthDate];
        yield '31st of April' => ['90043112349', PeselErrorEnum::InvalidBirthDate];
        yield '29th of February 1900 (not a leap year)' => ['00022912343', PeselErrorEnum::InvalidBirthDate];
        yield '29th of February 2023 (not a leap year)' => ['23222912348', PeselErrorEnum::InvalidBirthDate];
        yield 'all zeros' => ['00000000000', PeselErrorEnum::InvalidBirthDate];
    }

    public function testExceptionMessageDoesNotContainPesel(): void
    {
        try {
            PeselValueObject::fromString('44051401358');
            self::fail('Expected InvalidPeselException was not thrown.');
        } catch (InvalidPeselException $exception) {
            self::assertStringNotContainsString('44051401358', $exception->getMessage());
        }
    }

    public function testMatchesBirthDateComparesDateOnly(): void
    {
        $pesel = PeselValueObject::fromString('44051401359');

        self::assertTrue($pesel->matchesBirthDate(new DateTimeImmutable('1944-05-14')));
        self::assertTrue($pesel->matchesBirthDate(new DateTimeImmutable('1944-05-14 23:59:59', new DateTimeZone('UTC'))));
        self::assertFalse($pesel->matchesBirthDate(new DateTimeImmutable('1944-05-15')));
        self::assertFalse($pesel->matchesBirthDate(new DateTimeImmutable('2044-05-14')));
    }

    public function testMatchesGender(): void
    {
        $pesel = PeselValueObject::fromString('44051401359');

        self::assertTrue($pesel->matchesGender(GenderEnum::Male));
        self::assertFalse($pesel->matchesGender(GenderEnum::Female));
    }

    public function testEqualsComparesNormalizedValue(): void
    {
        $pesel = PeselValueObject::fromString('44051401359');

        self::assertTrue($pesel->equals(PeselValueObject::fromString(' 440514 01359 ')));
        self::assertFalse($pesel->equals(PeselValueObject::fromString('85831512348')));
    }
}

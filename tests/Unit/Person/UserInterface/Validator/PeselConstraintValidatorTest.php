<?php

declare(strict_types=1);

namespace App\Tests\Unit\Person\UserInterface\Validator;

use App\Person\UserInterface\Validator\PeselConstraint;
use App\Person\UserInterface\Validator\PeselConstraintValidator;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class PeselConstraintValidatorTest extends ConstraintValidatorTestCase
{
    private const TODAY = '2105-07-01 12:00:00';

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new PeselConstraintValidator($this->clockAt(self::TODAY));
    }

    /**
     * @dataProvider emptyValueProvider
     */
    public function testSkipsEmptyValue(?string $value): void
    {
        $this->validator->validate($value, new PeselConstraint());

        $this->assertNoViolation();
    }

    /**
     * @return iterable<string, array{?string}>
     */
    public static function emptyValueProvider(): iterable
    {
        yield 'null' => [null];
        yield 'empty string' => [''];
    }

    public function testAcceptsValidPesel(): void
    {
        $this->validator->validate('44051401359', new PeselConstraint());

        $this->assertNoViolation();
    }

    /**
     * @dataProvider invalidPeselProvider
     */
    public function testRaisesViolationMatchingDomainError(string $value, string $expectedMessage, string $expectedCode): void
    {
        $this->validator->validate($value, new PeselConstraint());

        $this->buildViolation($expectedMessage)
            ->setCode($expectedCode)
            ->assertRaised();
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function invalidPeselProvider(): iterable
    {
        yield 'invalid format' => ['4405140135a', 'person.pesel.invalid_format', PeselConstraint::INVALID_FORMAT_ERROR];
        yield 'invalid checksum' => ['44051401358', 'person.pesel.invalid_checksum', PeselConstraint::INVALID_CHECKSUM_ERROR];
        yield 'invalid birth date' => ['90131012346', 'person.pesel.invalid_birth_date', PeselConstraint::INVALID_BIRTH_DATE_ERROR];
    }

    public function testAcceptsBirthDateEqualToToday(): void
    {
        // PESEL of a person born on 2105-07-01 - the same day as the frozen clock
        $this->validator->validate('05470145138', new PeselConstraint());

        $this->assertNoViolation();
    }

    public function testRaisesViolationForBirthDateInFuture(): void
    {
        $this->validator = new PeselConstraintValidator($this->clockAt('2105-06-30 23:59:59'));
        $this->validator->initialize($this->context);

        $this->validator->validate('05470145138', new PeselConstraint());

        $this->buildViolation('person.pesel.birth_date_in_future')
            ->setCode(PeselConstraint::BIRTH_DATE_IN_FUTURE_ERROR)
            ->assertRaised();
    }

    public function testThrowsExceptionForNonStringValue(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate(44051401359, new PeselConstraint());
    }

    public function testThrowsExceptionForUnsupportedConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate('44051401359', new NotBlank());
    }

    private function clockAt(string $dateTime): ClockInterface
    {
        return new class(new DateTimeImmutable($dateTime)) implements ClockInterface {
            public function __construct(private readonly DateTimeImmutable $now)
            {
            }

            public function now(): DateTimeImmutable
            {
                return $this->now;
            }
        };
    }
}

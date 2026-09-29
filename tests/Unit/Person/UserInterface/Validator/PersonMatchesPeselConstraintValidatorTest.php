<?php

declare(strict_types=1);

namespace App\Tests\Unit\Person\UserInterface\Validator;

use App\Person\Model\Enum\GenderEnum;
use App\Person\UserInterface\Form\RegisterPersonFormData;
use App\Person\UserInterface\Validator\PersonMatchesPeselConstraint;
use App\Person\UserInterface\Validator\PersonMatchesPeselConstraintValidator;
use DateTimeImmutable;
use stdClass;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class PersonMatchesPeselConstraintValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ConstraintValidatorInterface
    {
        return new PersonMatchesPeselConstraintValidator();
    }

    public function testAcceptsDataMatchingPesel(): void
    {
        $this->validator->validate($this->formData(), new PersonMatchesPeselConstraint());

        $this->assertNoViolation();
    }

    public function testRaisesViolationOnBirthDateField(): void
    {
        $this->validator->validate(
            $this->formData(birthDate: new DateTimeImmutable('1944-05-15')),
            new PersonMatchesPeselConstraint(),
        );

        $this->buildViolation('person.birth_date.not_matching_pesel')
            ->atPath('property.path.birthDate')
            ->setCode(PersonMatchesPeselConstraint::BIRTH_DATE_NOT_MATCHING_ERROR)
            ->assertRaised();
    }

    public function testRaisesViolationOnGenderField(): void
    {
        $this->validator->validate($this->formData(gender: GenderEnum::Female), new PersonMatchesPeselConstraint());

        $this->buildViolation('person.gender.not_matching_pesel')
            ->atPath('property.path.gender')
            ->setCode(PersonMatchesPeselConstraint::GENDER_NOT_MATCHING_ERROR)
            ->assertRaised();
    }

    public function testRaisesViolationsOnBothFields(): void
    {
        $this->validator->validate(
            $this->formData(birthDate: new DateTimeImmutable('1944-05-15'), gender: GenderEnum::Female),
            new PersonMatchesPeselConstraint(),
        );

        $this->buildViolation('person.birth_date.not_matching_pesel')
            ->atPath('property.path.birthDate')
            ->setCode(PersonMatchesPeselConstraint::BIRTH_DATE_NOT_MATCHING_ERROR)
            ->buildNextViolation('person.gender.not_matching_pesel')
            ->atPath('property.path.gender')
            ->setCode(PersonMatchesPeselConstraint::GENDER_NOT_MATCHING_ERROR)
            ->assertRaised();
    }

    /**
     * @dataProvider notComparablePeselProvider
     */
    public function testSkipsComparisonWhenPeselIsMissingOrInvalid(?string $pesel): void
    {
        $this->validator->validate(
            $this->formData(pesel: $pesel, birthDate: new DateTimeImmutable('2000-01-01'), gender: GenderEnum::Female),
            new PersonMatchesPeselConstraint(),
        );

        $this->assertNoViolation();
    }

    /**
     * @return iterable<string, array{?string}>
     */
    public static function notComparablePeselProvider(): iterable
    {
        yield 'missing PESEL' => [null];
        yield 'invalid PESEL (reported by PeselConstraint)' => ['44051401358'];
    }

    public function testSkipsEmptyFields(): void
    {
        $this->validator->validate($this->formData(birthDate: null, gender: null), new PersonMatchesPeselConstraint());

        $this->assertNoViolation();
    }

    public function testThrowsExceptionForUnsupportedValue(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate(new stdClass(), new PersonMatchesPeselConstraint());
    }

    public function testThrowsExceptionForUnsupportedConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate($this->formData(), new NotBlank());
    }

    private function formData(
        ?string $pesel = '44051401359',
        ?DateTimeImmutable $birthDate = new DateTimeImmutable('1944-05-14'),
        ?GenderEnum $gender = GenderEnum::Male,
    ): RegisterPersonFormData {
        $data = new RegisterPersonFormData();
        $data->firstName = 'Jan';
        $data->lastName = 'Kowalski';
        $data->pesel = $pesel;
        $data->birthDate = $birthDate;
        $data->gender = $gender;

        return $data;
    }
}

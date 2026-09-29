<?php

declare(strict_types=1);

namespace App\Person\UserInterface\Validator;

use App\Person\Model\Exception\InvalidPeselException;
use App\Person\Model\ValueObject\PeselValueObject;
use App\Person\UserInterface\Form\RegisterPersonFormData;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * The comparison itself lives in PeselValueObject - this validator only maps the result to form fields.
 */
final class PersonMatchesPeselConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PersonMatchesPeselConstraint) {
            throw new UnexpectedTypeException($constraint, PersonMatchesPeselConstraint::class);
        }

        if (!$value instanceof RegisterPersonFormData) {
            throw new UnexpectedValueException($value, RegisterPersonFormData::class);
        }

        if (null === $value->pesel) {
            return;
        }

        try {
            $pesel = PeselValueObject::fromString($value->pesel);
        } catch (InvalidPeselException) {
            // Reported by PeselConstraint on the "pesel" field - nothing to compare with
            return;
        }

        if (null !== $value->birthDate && !$pesel->matchesBirthDate($value->birthDate)) {
            $this->context->buildViolation($constraint->birthDateNotMatchingMessage)
                ->atPath('birthDate')
                ->setCode(PersonMatchesPeselConstraint::BIRTH_DATE_NOT_MATCHING_ERROR)
                ->addViolation();
        }

        if (null !== $value->gender && !$pesel->matchesGender($value->gender)) {
            $this->context->buildViolation($constraint->genderNotMatchingMessage)
                ->atPath('gender')
                ->setCode(PersonMatchesPeselConstraint::GENDER_NOT_MATCHING_ERROR)
                ->addViolation();
        }
    }
}

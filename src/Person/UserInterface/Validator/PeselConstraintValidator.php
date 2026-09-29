<?php

declare(strict_types=1);

namespace App\Person\UserInterface\Validator;

use App\Person\Model\Enum\PeselErrorEnum;
use App\Person\Model\Exception\InvalidPeselException;
use App\Person\Model\ValueObject\PeselValueObject;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Translates PESEL domain errors into validation violations.
 * All PESEL rules live in PeselValueObject - only the "not in the future" rule is checked here,
 * because it depends on the current date.
 */
final class PeselConstraintValidator extends ConstraintValidator
{
    public function __construct(private readonly ClockInterface $clock)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PeselConstraint) {
            throw new UnexpectedTypeException($constraint, PeselConstraint::class);
        }

        // Empty values are handled by NotBlank
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        try {
            $pesel = PeselValueObject::fromString($value);
        } catch (InvalidPeselException $exception) {
            [$message, $code] = match ($exception->error()) {
                PeselErrorEnum::InvalidFormat => [$constraint->invalidFormatMessage, PeselConstraint::INVALID_FORMAT_ERROR],
                PeselErrorEnum::InvalidChecksum => [$constraint->invalidChecksumMessage, PeselConstraint::INVALID_CHECKSUM_ERROR],
                PeselErrorEnum::InvalidBirthDate => [$constraint->invalidBirthDateMessage, PeselConstraint::INVALID_BIRTH_DATE_ERROR],
            };

            $this->context->buildViolation($message)
                ->setCode($code)
                ->addViolation();

            return;
        }

        // Birth date is a calendar date: compare dates only, both built the same way as in PeselValueObject,
        // so time zone / DST offsets cannot shift the comparison.
        $today = new DateTimeImmutable($this->clock->now()->format('Y-m-d'));

        if ($pesel->birthDate() > $today) {
            $this->context->buildViolation($constraint->birthDateInFutureMessage)
                ->setCode(PeselConstraint::BIRTH_DATE_IN_FUTURE_ERROR)
                ->addViolation();
        }
    }
}

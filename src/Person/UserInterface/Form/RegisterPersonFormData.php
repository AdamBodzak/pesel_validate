<?php

declare(strict_types=1);

namespace App\Person\UserInterface\Form;

use App\Person\Application\RegisterPerson\RegisterPersonCommand;
use App\Person\Model\Enum\GenderEnum;
use App\Person\UserInterface\Validator\PersonMatchesPeselConstraint;
use App\Person\UserInterface\Validator\PeselConstraint;
use DateTimeImmutable;
use LogicException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Mutable by design - Symfony Form writes submitted values into it before validation.
 */
#[PersonMatchesPeselConstraint]
final class RegisterPersonFormData
{
    /**
     * Letters (any alphabet) separated by single spaces, hyphens or apostrophes, e.g. "Anna Maria", "Nowak-Kowalska", "O'Connor".
     */
    private const NAME_PATTERN = "/^\\p{L}+(?:[ \\-'’]\\p{L}+)*$/uD";

    #[Assert\NotBlank(message: 'person.first_name.not_blank')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'person.first_name.too_short', maxMessage: 'person.first_name.too_long')]
    #[Assert\Regex(pattern: self::NAME_PATTERN, message: 'person.first_name.invalid')]
    public ?string $firstName = null;

    #[Assert\NotBlank(message: 'person.last_name.not_blank')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'person.last_name.too_short', maxMessage: 'person.last_name.too_long')]
    #[Assert\Regex(pattern: self::NAME_PATTERN, message: 'person.last_name.invalid')]
    public ?string $lastName = null;

    #[Assert\NotBlank(message: 'person.pesel.not_blank')]
    #[PeselConstraint]
    public ?string $pesel = null;

    #[Assert\NotNull(message: 'person.birth_date.not_blank')]
    public ?DateTimeImmutable $birthDate = null;

    #[Assert\NotNull(message: 'person.gender.not_blank')]
    public ?GenderEnum $gender = null;

    /**
     * Must be called only for a submitted and valid form.
     */
    public function toCommand(): RegisterPersonCommand
    {
        if (null === $this->firstName || null === $this->lastName || null === $this->pesel
            || null === $this->birthDate || null === $this->gender) {
            throw new LogicException('Cannot create a command from incomplete form data.');
        }

        return new RegisterPersonCommand($this->firstName, $this->lastName, $this->pesel, $this->birthDate, $this->gender);
    }
}

<?php

declare(strict_types=1);

namespace App\Person\Model\ValueObject;

use App\Person\Model\Enum\GenderEnum;
use App\Person\Model\Exception\InvalidPeselException;
use DateTimeImmutable;

final readonly class PeselValueObject
{
    private const FORMAT_PATTERN = '/^\d{11}$/';
    private const CHECKSUM_WEIGHTS = [1, 3, 7, 9, 1, 3, 7, 9, 1, 3];
    private const CHECKSUM_DIGIT_POSITION = 10;
    private const GENDER_DIGIT_POSITION = 9;

    /**
     * The month field encodes the century: every 20 added to the month shifts the century.
     */
    private const MONTH_OFFSET_STEP = 20;
    private const CENTURY_BY_MONTH_OFFSET = [
        0 => 1900,
        20 => 2000,
        40 => 2100,
        60 => 2200,
        80 => 1800,
    ];

    private function __construct(
        private string $value,
        private DateTimeImmutable $birthDate,
        private GenderEnum $gender,
    ) {
    }

    /**
     * @throws InvalidPeselException
     */
    public static function fromString(string $value): self
    {
        $normalized = self::normalize($value);

        if (1 !== preg_match(self::FORMAT_PATTERN, $normalized)) {
            throw InvalidPeselException::invalidFormat();
        }

        $digits = array_map('intval', str_split($normalized));

        if (!self::hasValidChecksum($digits)) {
            throw InvalidPeselException::invalidChecksum();
        }

        return new self($normalized, self::decodeBirthDate($digits), self::decodeGender($digits));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function birthDate(): DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function gender(): GenderEnum
    {
        return $this->gender;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Removes all whitespace, including Unicode spaces (e.g. non-breaking space from copy-paste).
     * Invalid UTF-8 input makes preg_replace() return null - it is then treated as an invalid format.
     */
    private static function normalize(string $value): string
    {
        return preg_replace('/\s+/u', '', $value) ?? '';
    }

    /**
     * @param list<int> $digits
     */
    private static function hasValidChecksum(array $digits): bool
    {
        $sum = 0;
        foreach (self::CHECKSUM_WEIGHTS as $position => $weight) {
            $sum += $digits[$position] * $weight;
        }

        return (10 - $sum % 10) % 10 === $digits[self::CHECKSUM_DIGIT_POSITION];
    }

    /**
     * @param list<int> $digits
     *
     * @throws InvalidPeselException
     */
    private static function decodeBirthDate(array $digits): DateTimeImmutable
    {
        $yearOfCentury = $digits[0] * 10 + $digits[1];
        $encodedMonth = $digits[2] * 10 + $digits[3];
        $day = $digits[4] * 10 + $digits[5];

        $monthOffset = intdiv($encodedMonth, self::MONTH_OFFSET_STEP) * self::MONTH_OFFSET_STEP;
        $month = $encodedMonth - $monthOffset;
        $year = self::CENTURY_BY_MONTH_OFFSET[$monthOffset] + $yearOfCentury;

        if (!checkdate($month, $day, $year)) {
            throw InvalidPeselException::invalidBirthDate();
        }

        return new DateTimeImmutable(sprintf('%04d-%02d-%02d', $year, $month, $day));
    }

    /**
     * @param list<int> $digits
     */
    private static function decodeGender(array $digits): GenderEnum
    {
        return 0 === $digits[self::GENDER_DIGIT_POSITION] % 2 ? GenderEnum::Female : GenderEnum::Male;
    }
}

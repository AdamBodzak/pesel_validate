<?php

declare(strict_types=1);

namespace App\Person\UserInterface\Normalizer;

/**
 * Cleans up PESEL typed or pasted by a user before it is validated.
 * Shared by the registration form and the AJAX decode endpoint.
 */
final class PeselInputNormalizer
{
    /**
     * Removes all whitespace, including Unicode spaces (e.g. non-breaking space from copy-paste).
     * Invalid UTF-8 makes preg_replace() return null - the input is then returned unchanged
     * and rejected later by the PESEL format validation.
     */
    public function normalize(string $input): string
    {
        return preg_replace('/\s+/u', '', $input) ?? $input;
    }
}

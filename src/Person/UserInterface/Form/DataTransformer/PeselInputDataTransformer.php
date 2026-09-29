<?php

declare(strict_types=1);

namespace App\Person\UserInterface\Form\DataTransformer;

use App\Person\UserInterface\Normalizer\PeselInputNormalizer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<string, string>
 */
final class PeselInputDataTransformer implements DataTransformerInterface
{
    public function __construct(private readonly PeselInputNormalizer $normalizer)
    {
    }

    public function transform(mixed $value): string
    {
        return (string) $value;
    }

    public function reverseTransform(mixed $value): ?string
    {
        if (null === $value || '' === $value) {
            return null;
        }

        $normalized = $this->normalizer->normalize((string) $value);

        return '' === $normalized ? null : $normalized;
    }
}

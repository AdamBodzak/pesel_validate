<?php

declare(strict_types=1);

namespace App\Person\UserInterface\Http\Controller;

use App\Person\Model\ValueObject\PeselValueObject;
use App\Person\UserInterface\Http\Response\DecodePeselResponseFactory;
use App\Person\UserInterface\Normalizer\PeselInputNormalizer;
use App\Person\UserInterface\Validator\PeselConstraint;
use JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * AJAX hint for the registration form: decodes birth date and gender from PESEL.
 * Pure computation - it never reads the database, so it cannot reveal whether a PESEL is registered.
 */
#[AsController]
#[Route('/pesel/decode', name: 'pesel_decode', methods: ['POST'])]
final class DecodePeselController
{
    public const CSRF_TOKEN_ID = 'pesel_decode';
    private const CSRF_HEADER = 'X-CSRF-Token';

    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly PeselInputNormalizer $peselInputNormalizer,
        private readonly ValidatorInterface $validator,
        private readonly DecodePeselResponseFactory $responseFactory,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $token = new CsrfToken(self::CSRF_TOKEN_ID, (string) $request->headers->get(self::CSRF_HEADER));
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            return $this->responseFactory->invalidCsrfToken();
        }

        $input = $this->extractPesel($request);
        if (null === $input) {
            return $this->responseFactory->invalidRequest();
        }

        $pesel = $this->peselInputNormalizer->normalize($input);

        // The same constraints as in the form - messages stay consistent with the server-side validation
        $violations = $this->validator->validate($pesel, [
            new NotBlank(message: 'person.pesel.not_blank'),
            new PeselConstraint(),
        ]);
        if (count($violations) > 0) {
            return $this->responseFactory->invalidPesel((string) $violations->get(0)->getMessage());
        }

        return $this->responseFactory->decoded(PeselValueObject::fromString($pesel));
    }

    /**
     * Expects a JSON body: {"pesel": "..."} - PESEL is personal data and must never be sent in the URL.
     */
    private function extractPesel(Request $request): ?string
    {
        if ('json' !== $request->getContentType()) {
            return null;
        }

        try {
            $payload = json_decode($request->getContent(), true, 2, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($payload) && is_string($payload['pesel'] ?? null) ? $payload['pesel'] : null;
    }
}

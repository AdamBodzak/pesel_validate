<?php

declare(strict_types=1);

namespace App\Person\UserInterface\Http\Response;

use App\Person\Model\ValueObject\PeselValueObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * All texts are translated here - the frontend only displays them.
 * The PESEL itself is never echoed back.
 */
final class DecodePeselResponseFactory
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function decoded(PeselValueObject $pesel): JsonResponse
    {
        $gender = $pesel->gender()->value;

        return new JsonResponse([
            'birthDate' => $pesel->birthDate()->format('Y-m-d'),
            'gender' => $gender,
            'hint' => $this->translator->trans('person.pesel_hint.summary', [
                '%birth_date%' => $pesel->birthDate()->format('d.m.Y'),
                '%gender%' => $this->translator->trans('person.pesel_hint.gender.'.$gender),
            ]),
        ]);
    }

    public function invalidPesel(string $message): JsonResponse
    {
        return $this->error($message, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function invalidRequest(): JsonResponse
    {
        return $this->error($this->translator->trans('pesel_decode.invalid_request'), Response::HTTP_BAD_REQUEST);
    }

    public function invalidCsrfToken(): JsonResponse
    {
        return $this->error($this->translator->trans('pesel_decode.invalid_csrf_token'), Response::HTTP_FORBIDDEN);
    }

    private function error(string $message, int $status): JsonResponse
    {
        return new JsonResponse(['message' => $message], $status);
    }
}

<?php

declare(strict_types=1);

namespace App\Person\UserInterface\Http\Response;

use App\Person\UserInterface\Http\Controller\DecodePeselController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class RegisterPersonResponseFactory
{
    private const FLASH_REGISTERED = 'person.flash.registered';

    public function __construct(
        private readonly Environment $twig,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function form(FormInterface $form): Response
    {
        // 422 lets the browser and tests distinguish a rejected submission from the initial page
        $status = $form->isSubmitted() && !$form->isValid()
            ? Response::HTTP_UNPROCESSABLE_ENTITY
            : Response::HTTP_OK;

        return new Response(
            $this->twig->render('person/register.html.twig', [
                'form' => $form->createView(),
                'pesel_decode_csrf_token_id' => DecodePeselController::CSRF_TOKEN_ID,
            ]),
            $status,
        );
    }

    /**
     * Post/Redirect/Get: after a successful registration the user lands on the person list.
     */
    public function registered(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        // In Symfony 5.4 only the Session class (not SessionInterface) exposes the flash bag
        if ($session instanceof Session) {
            // Flash messages hold translation keys - they are translated in the template
            $session->getFlashBag()->add('success', self::FLASH_REGISTERED);
        }

        return new RedirectResponse($this->urlGenerator->generate('person_list'));
    }
}

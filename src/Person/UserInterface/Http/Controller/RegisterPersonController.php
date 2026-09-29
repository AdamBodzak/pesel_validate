<?php

declare(strict_types=1);

namespace App\Person\UserInterface\Http\Controller;

use App\Person\Application\RegisterPerson\RegisterPersonHandler;
use App\Person\Model\Exception\PeselAlreadyRegisteredException;
use App\Person\UserInterface\Form\RegisterPersonFormData;
use App\Person\UserInterface\Form\RegisterPersonFormType;
use App\Person\UserInterface\Http\Response\RegisterPersonResponseFactory;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsController]
#[Route('/', name: 'person_register', methods: ['GET', 'POST'])]
final class RegisterPersonController
{
    private const ALREADY_REGISTERED_MESSAGE = 'person.pesel.already_registered';

    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly RegisterPersonHandler $registerPersonHandler,
        private readonly RegisterPersonResponseFactory $responseFactory,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory->create(RegisterPersonFormType::class, new RegisterPersonFormData());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var RegisterPersonFormData $data */
            $data = $form->getData();

            try {
                $this->registerPersonHandler->handle($data->toCommand());

                return $this->responseFactory->registered($request);
            } catch (PeselAlreadyRegisteredException) {
                $form->get('pesel')->addError(new FormError(
                    $this->translator->trans(self::ALREADY_REGISTERED_MESSAGE, [], 'validators'),
                ));
            }
        }

        return $this->responseFactory->form($form);
    }
}

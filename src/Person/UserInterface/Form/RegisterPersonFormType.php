<?php

declare(strict_types=1);

namespace App\Person\UserInterface\Form;

use App\Person\Model\Enum\GenderEnum;
use App\Person\UserInterface\Form\DataTransformer\PeselInputDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<RegisterPersonFormData>
 */
final class RegisterPersonFormType extends AbstractType
{
    public function __construct(private readonly PeselInputDataTransformer $peselInputDataTransformer)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'person.form.first_name',
                'attr' => ['autocomplete' => 'given-name'],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'person.form.last_name',
                'attr' => ['autocomplete' => 'family-name'],
            ])
            ->add('pesel', TextType::class, [
                'label' => 'person.form.pesel',
                'attr' => ['inputmode' => 'numeric', 'autocomplete' => 'off'],
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'person.form.birth_date',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'invalid_message' => 'person.birth_date.invalid',
            ])
            ->add('gender', EnumType::class, [
                'label' => 'person.form.gender',
                'class' => GenderEnum::class,
                'expanded' => true,
                'choice_label' => static fn (GenderEnum $gender): string => 'person.gender.'.$gender->value,
                'invalid_message' => 'person.gender.invalid',
            ]);

        $builder->get('pesel')->addModelTransformer($this->peselInputDataTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegisterPersonFormData::class,
            'csrf_token_id' => 'register_person',
        ]);
    }
}

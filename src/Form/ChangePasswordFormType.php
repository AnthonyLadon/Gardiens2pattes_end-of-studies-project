<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ChangePasswordFormType extends AbstractType
{

    // Injection du service TranslatorInterface afin de pouvoir traduire les labels
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'label' => $this->translator->trans('Nouveau mot de passe', [], 'form'),
                'options' => [
                    'attr' => [
                        'autocomplete' => 'Nouveau mot de passe',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => $this->translator->trans('Entrez un nouveau mot de passe', [], 'form'),
                        ]),
                        new Length([
                            'min' => 7,
                            'minMessage' => 'Your password should be at least {{ limit }} characters',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                        // Regex pour obliger l'utilisateur à créer un mot de passe solide (minimum 1 majuscule, 1 caractere special, 1 chiffre)
                        new Regex(
                            "/^(?=.*\d)(?=.*[A-Z])(?=.*[@#$%])(?!.*(.)\1{2}).*[a-z]/m",
                            'Désolé votre mot de passe n\'est pas valide, veuillez le modifier en veillant à respecter les contraintes de complexité'
                        )
                    ],
                    'label' => $this->translator->trans('Nouveau mot de passe', [], 'form'),
                ],
                'second_options' => [
                    'label' => $this->translator->trans('Repetez le mot de passe', [], 'form'),
                ],
                'invalid_message' => 'Les deux mots de passe doivent correspondre',
                'label' => $this->translator->trans('Les deux mots de passe doivent correspondre', [], 'form'),
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}

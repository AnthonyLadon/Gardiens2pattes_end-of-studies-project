<?php

namespace App\Form;

use App\Entity\Utilisateurs;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RegistrationGardienFormType extends AbstractType
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
            ->add('email', EmailType::class, [
                'label' => 'Email *',
                'label' => $this->translator->trans('Email', [], 'form'),
                'required' => true,
                'invalid_message' => 'Veuillez entrer une adresse email valide'
                ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'label' => $this->translator->trans('Prenom', [], 'form'),
                'required' => true
                ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'label' => $this->translator->trans('Nom', [], 'form'),
                'required' => true
                ])
            ->add('pseudo', null, [
                'label' => 'Pseudo *',
                'label' => $this->translator->trans('Pseudo', [], 'form'),
                'required' => true])
            ->add('telNum', TextType::class, [
                'label' => 'Tél.',
                'label' => $this->translator->trans('Tél.', [], 'form'),
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'maxlength' => 10, // limite de chiffres que l'on peut entrer dans le champ
                ],
            ])
            ->add('rue', TextType::class, [
                'label' => 'Rue *',
                'label' => $this->translator->trans('Rue', [], 'form'),
                'required' => true,
                'mapped' => false,
                'invalid_message' => 'Veuillez entrer une rue valide'
                ])
            ->add('numero', TextType::class, [
                'label' => 'Numéro *',
                'label' => $this->translator->trans('Numero', [], 'form'),
                'required' => true,
                'mapped' => false,
                'attr' => [
                    'maxlength' => 6, // limite de chiffres que l'on peut entrer dans le champ
                 ],
                 'invalid_message' => 'Veuillez entrer un numéro valide',
                ])
            ->add ('CodePostal', NumberType::class, [
                'label' => 'Code postal *',
                'label' => $this->translator->trans('Code postal', [], 'form'),
                'required' => true,
                'mapped' => false,
                'attr' => [
                    'minLength' => 4, // minimum de chiffres que l'on doit entrer
                    'maxlength' => 4, // limite de chiffres que l'on peut entrer dans le champ
                    ],
                    'invalid_message' => 'Veuillez entrer un code postal valide',
                ])
            ->add ('commune', textType::class, [
                'label' => 'Commune *',
                'required' => true,
                'mapped' => false,
                'invalid_message' => 'Veuillez entrer une commune valide',
                ])
            ->add('bio', TextareaType::class, [
                'label' => 'Ma présentation',
                'label' => $this->translator->trans('Ma présentation', [], 'form'),
                'required' => false,
                'mapped' => false,
                ])
            ->add('Iban', TextType::class, [
                'label' => 'IBAN *',
                'label' => $this->translator->trans('Iban', [], 'form'),
                'required' => true,
                'mapped' => false,
                'attr' => [
                    'maxlength' => 16, // limite de chiffres que l'on peut entrer dans le champ
                    ],
                    'invalid_message' => 'Veuillez entrer un IBAN valide',
                ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'J\'accepte les conditions',
                'label' => $this->translator->trans('J\'accepte les conditions', [], 'form'),
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions',
                    ]),
                ],
            ])
            // Utilisation du type RepeatedType -> l'utilisateur doit entrer 2 fois
            // son mot de passe (Si match des 2 MDP -> validé)
            ->add('plainPassword', RepeatedType::class, [
                'first_options'  => ['label' => 'Veuillez entrer un mot de passe *'],
                'second_options' => ['label' => 'Repetez le mot de passe *'],
                'type' => PasswordType::class,
                'invalid_message' => 'Attention! Les deux mots de passe doivent correspondre',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe'
                    ]),
                    new Length([
                        // contrainte de longueur du mot de passe
                        'min' => 7,
                        'minMessage' => 'Votre mot de passe doit contenir au minimum {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                    // Regex pour obliger l'utilisateur à créer un mot de passe solide (minimum 1 majuscule, 1 caractere special, 1 chiffre)
                    new Regex("/^(?=.*\d)(?=.*[A-Z])(?=.*[@#$%])(?!.*(.)\1{2}).*[a-z]/m",
                    'Désolé votre mot de passe n\'est pas valide, veuillez le modifier en veillant à respecter les contraintes de complexité'
                    )
                ],
            ])
            // récupération des données du formulaire poiur afficher les erreurs dans le formulaire
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                foreach ($form->getErrors(true) as $error) {
                    // Affichez l'erreur associée à chaque champ
                    $form->addError(new FormError($error->getMessage()));
                }
            });
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateurs::class,
        ]);
    }
}

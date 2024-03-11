<?php

namespace App\Form;

use App\Entity\Commentaires;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class CommentaireType extends AbstractType
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

            ->add('note', ChoiceType::class, [
                'choices' => [
                    'Choisir une note' => null,
                    '1 - insufisant' => 1,
                    '2 - moyen' => 2,
                    '3 - bien' => 3,
                    '4 - très bien' => 4,
                    '5 - Exellent!' => 5,
                ],
                'mapped' => false,
                'label' => $this->translator->trans('Notez le prestataire', [], 'form'),
                'constraints' => [
                    new Range(['min' => 1, 'max' => 5]),
                ],
            ])
            ->add('titre', null, [
                'label' => $this->translator->trans('Titre', [], 'form'),
            ])
            ->add('commentaire', null, [
                'label' => $this->translator->trans('Commentaire', [], 'form'),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaires::class,
        ]);
    }
}

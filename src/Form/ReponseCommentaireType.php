<?php

namespace App\Form;

use App\Entity\Commentaires;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ReponseCommentaireType extends AbstractType
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
            ->add('ReponseGardien', TextareaType::class, [
                'label' => 'Votre réponse',
                'label' => $this->translator->trans('Votre réponse', [], 'form'),
                'attr' => [
                    'placeholder' => 'Votre réponse'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
                'label' => $this->translator->trans('Valider', [], 'form'),
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
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

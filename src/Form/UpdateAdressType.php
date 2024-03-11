<?php

namespace App\Form;

use App\Entity\Adresses;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class UpdateAdressType extends AbstractType
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
        ->add('rue', TextType::class, [
            'label' => 'Rue',
            'label' => $this->translator->trans('Rue', [], 'form'),
            'required' => false,
            ])
        ->add('numero', NumberType::class, [
            'label' => 'Numéro',
            'label' => $this->translator->trans('Numero', [], 'form'),
            'required' => false,
            'attr' => [
                'maxlength' => 6, // limite de chiffres que l'on peut entrer dans le champ
                ],
            ])
        ->add('commune', TextType::class, [
            'label' => 'Commune',
            'label' => $this->translator->trans('Commune', [], 'form'),
            'data' => $options['data']->getCommune(),
            'attr' => [
                'maxlength' => 40, // limite de caractères que l'on peut entrer dans le champ
                ],
        ])
        ->add('codePostal', NumberType::class, [
            'label' => 'Code postal',
            'label' => $this->translator->trans('Code postal', [], 'form'),
            'data' => $options['data']->getCodePostal(),
            'attr' => [
                'maxlength' => 4, // limite de chiffres que l'on peut entrer dans le champ
                ],
        ])
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Adresses::class,
        ]);
    }
}

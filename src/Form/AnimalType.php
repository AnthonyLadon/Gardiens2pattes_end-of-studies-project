<?php

namespace App\Form;

use App\Entity\Animaux;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AnimalType extends AbstractType
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
            ->add('nom', TextType::class, [
                'label' => $this->translator->trans('Nom', [], 'form'),
                'required' => true,
            ])
            ->add('age', NumberType::class, [
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => $this->translator->trans('Description', [], 'form'),
                'required' => false,
            ])
            ->add('categorieAnimal', null, [
                'label' => $this->translator->trans('Categorie de l\'animal', [], 'form'),
                'choice_label' => 'nom',
                'required' => true,
            ])
            ->add('poids', NumberType::class, [
                'label' => $this->translator->trans('Poids', [], 'form'),
                'required' => false,
                'label' => 'Poids (en kg)'
            ])
            ->add('antecedentsMedicaux', TextareaType::class, [
                'label' => $this->translator->trans('Antédecents medicaux', [], 'form'),
                'required' => false,
            ])
            ->add('sociabilite', TextType::class, [
                'label' => $this->translator->trans('Sociabilite', [], 'form'),
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Animaux::class,
        ]);
    }
}

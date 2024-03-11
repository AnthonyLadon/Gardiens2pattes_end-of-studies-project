<?php

namespace App\Form;

use App\Entity\Prestataires;
use App\Entity\CategoriesAnimaux;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class GardienType extends AbstractType
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
        ->add('Iban', TextType::class, [
            'label' => 'Mon IBAN *',
            'label' => $this->translator->trans('Mon IBAN', [], 'form'),
            'required' => true,
            'mapped' => true,
            'attr' => [
                'maxlength' => 16, // limite de chiffres que l'on peut entrer dans le champ
                ],
                'invalid_message' => 'Veuillez entrer un IBAN valide',
            ])
        ->add('gardeDomicile', null, [
            'label' => 'Je propose mes services de garde à domicile',
            'label' => $this->translator->trans('Je propose mes services de garde à domicile', [], 'form'),
        ])
        ->add('vehicule', null, [
            'label' => 'Je possède un véhicule',
            'label' => $this->translator->trans('Je possède un véhicule', [], 'form'),
        ])
        ->add('jardin', null, [
            'label' => 'Je possède un jardin',
            'label' => $this->translator->trans('Je possède un jardin', [], 'form'),
        ])
        ->add('tarif', null, [
            'label' => 'Tarif hebergement par jour (€)',
            'label' => $this->translator->trans('Tarif hebergement par jour (€)', [], 'form'),
        ])
        ->add('soins_veto', null, [
            'label' => 'Je propose mes services de soins vétérinaires',
            'label' => $this->translator->trans('Je propose mes services de soins vétérinaires', [], 'form'),
        ])
        ->add('tarif_deplacement', null, [
            'label' => 'Tarif par déplacement (€)',
            'label' => $this->translator->trans('Tarif par déplacement (€)', [], 'form'),
        ])
        ->add('tarif_promenade', null, [
            'label' => 'Tarif promenade par jour (€)',
            'label' => $this->translator->trans('Tarif par promenade (€)', [], 'form'),
        ]) 
        ->add('bio', null, [
            'label' => 'Ma présentation',
            'label' => $this->translator->trans('Ma présentation', [], 'form'),
        ])
        ->add('zoneGardiennage', null, [
            'label' => 'Zone de gardiennage à partir de mon domicile (en km)',
            'label' => $this->translator->trans('Zone de gardiennage à partir de mon domicile (en km)', [], 'form'),
        ])
        ->add('specialisations',EntityType::class,[            
            'class' => CategoriesAnimaux::class,
            'choice_label' => 'nom',
            'multiple' => true,
            'label' => 'Mes catégories d\'animal prises en charge',
            'label' => $this->translator->trans('Mes catégories d\'animal prises en charge', [], 'form'),
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prestataires::class,
        ]);
    }
}

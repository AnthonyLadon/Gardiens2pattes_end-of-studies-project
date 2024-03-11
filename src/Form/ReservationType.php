<?php

namespace App\Form;

use App\Entity\Animaux;
use App\Entity\Reservation;
use App\Repository\AnimauxRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ReservationType extends AbstractType
{

    // Injection du service TranslatorInterface afin de pouvoir traduire les labels
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        // récupération des variables passées en option depuis le controller
        $maitre = $options['maitre'];
        $prestataire = $options['prestataire'];

        $builder
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début *',
                'label' => $this->translator->trans('Date de début', [], 'form'),
                'required' => true
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin *',
                'label' => $this->translator->trans('Date de fin', [], 'form'),
                'required' => true
            ])
            ->add('details', TextareaType::class, [
                'label' => 'Ajouter des details utiles pour le gardien',
                'label' => $this->translator->trans('Ajouter des details utiles pour le gardien', [], 'form'),
                'required' =>  false
            ])
            ->add('nbPassages', ChoiceType::class, [
                'label' => 'Passages à domicile (par jour)',
                'label' => $this->translator->trans('Passages à domicile (par jour)', [], 'form'),
                'required' => false,
                'choices' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3
                ],
                // si $prestataire->getTarifDeplacement est null ou egal à 0 on empeche de choisir le nombre de passages
                'disabled' => $prestataire->getTarifDeplacement() == null || $prestataire->getTarifDeplacement() == 0
            ])
            ->add('hebergement', null, [
                'label' => 'Hebergement de l\'animal',
                'label' => $this->translator->trans('Hebergement de l\'animal', [], 'form'),
                'required' => false,
                // si $prestataire->getTarif est null ou egal à 0 on empeche de choisir le nombre de passages
                'disabled' => $prestataire->getTarif() == null || $prestataire->getTarif() == 0,
            ])
            ->add('nbPromenades', ChoiceType::class, [
                'label' => 'Promenade de l\'animal (nombre de promenades par jour)',
                'label' => $this->translator->trans('Promenade de l\'animal (nombre de promenades par jour)', [], 'form'),
                'required' => false,
                'choices' => [
                    '1' => 1,
                    '2' => 2,
                    '3' => 3
                ],
                // si $prestataire->getPromenade est null ou egal à 0 on empeche de choisir le nombre de promenades
                'disabled' => $prestataire->getTarifPromenade() == null || $prestataire->getTarifPromenade() == 0
            ])
            // Afficher les animaux du maitre connecté
            ->add('animal', EntityType::class, [
                'class' => Animaux::class,
                'required' => true,
                'label' => $this->translator->trans('Animal', [], 'form'),
                'choice_label' => 'nom',
                'label' => 'Animal *',
                'attr' => [
                    'placeholder' => 'Animal'
                ],
                // récupération des animaux du maitre connecté
                'query_builder' => function(AnimauxRepository $animauxRep) use ($maitre) {
                    // trouver les animaux du maitre connecté
                    return $animauxRep->createQueryBuilder('a')
                        ->where('a.maitre = :maitre')
                        ->setParameter('maitre', $maitre);
                },
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
            'data_class' => Reservation::class,
        ]);
        $resolver->setRequired(['maitre']);
        $resolver->setRequired(['prestataire']);
    }
}

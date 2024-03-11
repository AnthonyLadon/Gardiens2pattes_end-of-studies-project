<?php

namespace App\Form;

use App\Entity\Images;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ImageType extends AbstractType
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
            ->add('imageFile', FileType::class, [
                "label" => "Image",
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([

                        // /!\ Configuration de 'upload_max_filesize = 5M' dans php.ini (serveur php)
                        // commande -> "php --ini" pour voir le chemin du fichier php.ini

                        'maxSize' => '10M',  // Taile max du fichier uplodé 
                        'mimeTypes' => [
                            'image/png', 
                            'image/jpg',
                            'image/jpeg',
                            'image/webp',
                            'image/avif',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploder une image valide (Taille max = 5mo)',
                    ])
                ],
            ])
            ->add('enregistrer', SubmitType::class, 
            [
                'label' => 'enregistrer'
            ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Images::class,
        ]);
    }
}

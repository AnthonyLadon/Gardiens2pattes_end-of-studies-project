<?php

namespace App\Controller\Admin;

use App\Entity\Commentaires;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CommentairesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Commentaires::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('titre')
            ->setFormTypeOptions([
                'disabled' => true
            ]),
            TextareaField::new('commentaire')
            ->setFormTypeOptions([
                'disabled' => true
            ]),
            DateTimeField::new('date')
            ->setFormTypeOptions([
                'disabled' => true
            ]),
            BooleanField::new('en_avant')
            ->setCustomOption('renderAsSwitch', false),
            TextField::new('ReponseGardien')
            ->setFormTypeOptions([
                'disabled' => true
            ])
            ->setFormTypeOptions([
                'disabled' => true
            ]),
        ];
    }

        // supprime le bouton "creer signalement"
        public function configureActions(Actions $actions): Actions
        {
            return $actions
                ->remove(Crud::PAGE_INDEX, Action::NEW);
        }
}

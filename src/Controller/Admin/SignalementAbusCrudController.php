<?php

namespace App\Controller\Admin;

use App\Entity\SignalementAbus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class SignalementAbusCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SignalementAbus::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        // configuration de l'affichage des signalements      
        return $crud
            ->setEntityLabelInSingular('Signalement')
            ->setEntityLabelInPlural('Signalements')
            ->setSearchFields(['id', 'estTraite', 'date', 'commentaire'])
            ->setPaginatorPageSize(10);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            BooleanField::new('estTraite')
            ->setCustomOption('renderAsSwitch', false),
            DateField::new('date'),
            AssociationField::new('commentaire'),
        ];
    }

        // supprime le bouton "creer signalement"
        public function configureActions(Actions $actions): Actions
        {
            return $actions
                ->remove(Crud::PAGE_INDEX, Action::NEW);
        }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateurs;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UtilisateursCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Utilisateurs::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setSearchFields(['id', 'email', 'pseudo', 'nom', 'prenom', 'banni', 'roles', 'dateInscription'])
            ->setPaginatorPageSize(10);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('email')
            ->setFormTypeOptions([
                'disabled' => true
            ]),
            TextField::new('pseudo')
            ->setFormTypeOptions([
                'disabled' => true
            ]),
            TextField::new('nom')
            ->setFormTypeOptions([
                'disabled' => true
            ]),
            TextField::new('prenom')
            ->setFormTypeOptions([
                'disabled' => true
            ]),
            BooleanField::new('banni')
            ->setCustomOption('renderAsSwitch', false),
            ArrayField::new('roles'),
            DateField::new('dateInscription')
            ->setFormTypeOptions([
                'disabled' => true
            ]),
        ];
    }

    // supprime le bouton "creer utilisateur"
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW);
    }
}

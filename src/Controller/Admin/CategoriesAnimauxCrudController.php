<?php

namespace App\Controller\Admin;

use App\Entity\CategoriesAnimaux;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CategoriesAnimauxCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CategoriesAnimaux::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom'),
            BooleanField::new('exotique')
            ->setCustomOption('renderAsSwitch', false),
        ];
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Qualite;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;

final class QualiteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Qualite::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Qualité')
            ->setEntityLabelInPlural('Qualités')
            ->setPageTitle(Crud::PAGE_INDEX, 'Qualités de séance')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer une qualité')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la qualité')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail de la qualité')
            // 🔒 accès réservé
            ->setEntityPermission('ROLE_SUPER_ADMIN');
    }

    public function configureFields(string $pageName): iterable
    {

        yield TextField::new('label', 'Libellé')
            ->setHelp('Ex: "IMAX 3D", "Version Originale", "Standard"…');

        // L’entité stocke prix en DECIMAL(string) → MoneyField sait lire via getPrix()
        yield MoneyField::new('prix', 'Prix')
            ->setCurrency('EUR')
            ->setNumDecimals(2)
            ->setStoredAsCents(false)   // <<< important
            ->setHelp('Tarif appliqué aux séances de cette qualité.');
    }
}

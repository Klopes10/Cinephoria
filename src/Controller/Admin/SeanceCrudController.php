<?php

namespace App\Controller\Admin;

use App\Entity\Seance;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class SeanceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Seance::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des séances')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la séance')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail de la séance');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions;
    }

  public function configureFields(string $pageName): iterable
{
    yield DateTimeField::new('dateHeureDebut', 'Date et heure de début');
    yield DateTimeField::new('dateHeureFin', 'Date et heure de fin');
    yield MoneyField::new('prix', 'Prix')->setCurrency('EUR');
    yield AssociationField::new('cinema', 'Cinéma');
    yield AssociationField::new('film', 'Film');
    yield AssociationField::new('salle', 'Salle');
    
    // Champs personnalisés
    yield IntegerField::new('nombrePlacesSalle', 'Places totales')->onlyOnIndex();
    yield IntegerField::new('placesDisponible', 'Places restantes')->onlyOnIndex();
}
}

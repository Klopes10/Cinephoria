<?php

namespace App\Controller\Admin;

use App\Entity\Seance;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;



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
        yield  DateField::new('date', '📅 Date de la séance')
    ->setFormat('dd/MM/yyyy');
        yield TimeField::new('heureDebut', '🕒 Heure de début')
    ->setFormat('HH:mm');
        yield TimeField::new('heureFin', '🕕 Heure de fin')
    ->setFormat('HH:mm');

       






        yield MoneyField::new('prix', '💰 Prix')->setCurrency('EUR');
        yield AssociationField::new('cinema', '🎦 Cinéma');
        yield AssociationField::new('film', '🎬 Film');
        yield AssociationField::new('salle', '🏛️ Salle');

        yield IntegerField::new('nombrePlacesSalle', '🪑 Places totales')->onlyOnIndex();
        yield IntegerField::new('placesDisponible', '🎟️ Places restantes')->onlyOnIndex();

        yield Field::new('qualite', '🎞️ Qualité')->onlyOnDetail();
    }
}

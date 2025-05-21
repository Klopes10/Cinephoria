<?php

namespace App\Controller\Admin;

use App\Entity\Seance;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SeanceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Seance::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            DateTimeField::new('dateHeureDebut'),
            DateTimeField::new('dateHeureFin'),
            TextField::new('qualite'),
            IntegerField::new('placesDisponible'),
            MoneyField::new('prix')->setCurrency('EUR'),
            AssociationField::new('film'),
            AssociationField::new('salle'),
        ];
    }
}

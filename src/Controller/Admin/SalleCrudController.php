<?php

namespace App\Controller\Admin;

use App\Entity\Salle;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class SalleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Salle::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom', 'Nom de la salle'),
            IntegerField::new('nombrePlaces', 'Nombre de places'),

            // Qualité reliée
            AssociationField::new('qualite', 'Qualité de projection')->autocomplete(),

            AssociationField::new('cinema', 'Cinéma associé')->autocomplete(),
            DateTimeField::new('createdAt', 'Créée le')->hideOnForm(),
        ];
    }
}

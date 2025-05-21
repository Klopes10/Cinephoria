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
            TextField::new('qualite', 'Qualité de projection'),
    
            AssociationField::new('cinema', 'Cinéma associé'),
    
            DateTimeField::new('createdAt', 'Créée le')->hideOnForm(),
        ];
    }
    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}

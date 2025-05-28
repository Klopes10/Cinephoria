<?php

namespace App\Controller\Admin;

use App\Entity\Siege;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class SiegeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Siege::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('numero', 'Numéro de siège'),
            BooleanField::new('isPMR', 'Place PMR'),
            BooleanField::new('IsReserved', 'Réservé'),
            AssociationField::new('seance'),
        ];
    }
}

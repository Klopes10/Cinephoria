<?php

namespace App\Controller\Admin;

use App\Entity\Incident;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class IncidentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Incident::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Incident')
            ->setEntityLabelInPlural('Incidents');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('salle')->setLabel('Salle'),

            TextField::new('cinemaName', 'Cinéma')
                ->onlyOnIndex()
                ->setSortable(false),

            TextareaField::new('description')
                ->setLabel('Description'),

            DateTimeField::new('createdAt')
                ->setLabel('Signalé le'),

            BooleanField::new('resolu')
                ->setLabel('Résolu ?')
                ->renderAsSwitch(true), // ✅ toggle activable
        ];
    }
}

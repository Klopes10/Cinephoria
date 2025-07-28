<?php

namespace App\Controller\Admin;

use App\Entity\Avis;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;

class AvisCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Avis::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Avis')
            ->setEntityLabelInPlural('Avis')
            ->showEntityActionsInlined(false); // ⬅️ actions "Edit/Delete" dans le menu ...
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('valide')->setLabel('Validé ?'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('user')->setLabel('Utilisateur')->onlyOnIndex(),
            AssociationField::new('film')->setLabel('Film')->onlyOnIndex(),
            IntegerField::new('noteSur5')->setLabel('Note sur 5')->onlyOnIndex(),
            TextareaField::new('commentaire')->setLabel('Commentaire')->onlyOnIndex(),
            DateTimeField::new('createdAt')->setLabel('Déposé le')->onlyOnIndex(),
            BooleanField::new('valide')
                ->setLabel('Validé')
                ->renderAsSwitch(true)
                ->onlyOnIndex(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW); // autorise Edit et Delete, supprime juste NEW
    }
}

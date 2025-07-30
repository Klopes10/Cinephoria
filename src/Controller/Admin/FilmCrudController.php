<?php

namespace App\Controller\Admin;

use App\Entity\Film;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class FilmCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Film::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Films')
            ->setEntityLabelInSingular('Film')
            ->setDefaultSort(['titre' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            

            TextField::new('titre', 'Titre')->setRequired(true),
            TextareaField::new('synopsis', 'Synopsis')->hideOnIndex(),
            IntegerField::new('ageMinimum', 'Âge minimum')
                ->setRequired(false)
                ->formatValue(function ($value, $entity) {
                    return $value === null ? 'Tout public' : $value . ' ans';
                }),

            BooleanField::new('coupDeCoeur', 'Coup de cœur'),

            AssociationField::new('genre', 'Genre')
                ->setRequired(true),

            ImageField::new('affiche', 'Affiche')
                ->setUploadDir('public/uploads/affiches/')
                ->setBasePath('uploads/affiches')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
                ->setRequired(false),
        ];
    }
}

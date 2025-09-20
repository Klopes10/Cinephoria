<?php

namespace App\Controller\Admin;

use App\Entity\Film;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField; // ⬅️ ajout

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

            // ⬇️ Date de publication (jour / mois / année)
            DateField::new('datePublication', 'Date de publication')
                ->setRequired(true)
                ->setFormat('dd/MM/yyyy')        // affichage
                ->renderAsNativeWidget(false),   // joli widget (ou true pour natif)

            TextareaField::new('synopsis', 'Synopsis')->hideOnIndex(),

            IntegerField::new('ageMinimum', 'Âge minimum')
                ->setRequired(false)
                ->formatValue(fn($value) => $value === null ? 'Tout public' : $value . ' ans'),

            BooleanField::new('coupDeCoeur', 'Coup de cœur'),

            AssociationField::new('genre', 'Genre')->setRequired(true),

            ImageField::new('affiche', 'Affiche')
            ->setUploadDir('public/uploads/affiches')  // sans trailing slash
            ->setBasePath('/uploads/affiches')         // ABSOLU (important en /admin/*)
            ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
            ->setRequired(false),
        ];
    }
}

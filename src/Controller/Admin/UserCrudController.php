<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setPageTitle(Crud::PAGE_INDEX, '👤 Liste des utilisateurs')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier un utilisateur');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        yield EmailField::new('email', 'Email');
        yield TextField::new('username', 'Nom d’utilisateur');
        yield TextField::new('name', 'Nom')->onlyOnForms();
        yield TextField::new('forname', 'Prénom')->onlyOnForms();

        yield ChoiceField::new('roles', 'Rôle')
            ->allowMultipleChoices(false)
            ->autocomplete()
            ->setChoices([
                'Client' => 'ROLE_USER',
                'Employé' => 'ROLE_ADMIN',
            ]);

        yield ArrayField::new('roles', 'Rôles')->onlyOnIndex();
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class UserCrudController extends AbstractCrudController
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

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
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier un utilisateur')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer un nouvel utilisateur');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('username', "Nom d’utilisateur");
        yield TextField::new('name', 'Nom');
        yield TextField::new('forname', 'Prénom');
        yield EmailField::new('email', 'Email');

       
        yield ChoiceField::new('singleRole', 'Rôle')
            ->setChoices([
                'Client'         => 'ROLE_USER',
                'Employé'        => 'ROLE_ADMIN',
                'Administrateur' => 'ROLE_SUPER_ADMIN',
            ])
            ->setRequired(true)
            ->renderExpanded(false)
            ->renderAsNativeWidget();

        
        $passwordConstraints = [
            new Length([
                'min' => 8,
                'minMessage' => 'Votre mot de passe doit faire au moins {{ limit }} caractères',
                'max' => 4096,
            ]),
            new Regex([
                'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
                'message' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
            ]),
        ];

        if ($pageName === Crud::PAGE_NEW) {
            array_unshift($passwordConstraints, new NotBlank(['message' => 'Veuillez saisir un mot de passe']));
        }

        yield TextField::new('plainPassword', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->setFormTypeOptions([
                'mapped'      => false, 
                'required'    => $pageName === Crud::PAGE_NEW,
                'attr'        => ['autocomplete' => 'new-password'],
                'help'        => "8 caractères min., dont 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial.",
                'constraints' => $passwordConstraints,
            ])
            ->onlyOnForms();
    }

    // On écoute la soumission pour hacher le mot de passe si rempli
    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $builder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        $this->addPasswordHashingListener($builder);
        return $builder;
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $builder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        $this->addPasswordHashingListener($builder);
        return $builder;
    }

    private function addPasswordHashingListener(FormBuilderInterface $builder): void
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var User $user */
            $user = $event->getData();
            $form = $event->getForm();

            // "plainPassword" est non mappé : on le lit depuis le form
            $plain = $form->get('plainPassword')->getData();

            if (is_string($plain) && $plain !== '') {
                $hash = $this->passwordHasher->hashPassword($user, $plain);
                $user->setPassword($hash);
            }
        });
    }
}

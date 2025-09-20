<?php

namespace App\Controller\Admin;

use App\Entity\Avis;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class AvisCrudController extends AbstractCrudController
{
    public function __construct(private AdminUrlGenerator $urlGenerator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Avis::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Avis')
            ->setEntityLabelInPlural('Avis')
            ->showEntityActionsInlined(false);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('valide')->setLabel('Validé ?'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('user', 'Utilisateur')->onlyOnIndex(),
            AssociationField::new('film', 'Film')->onlyOnIndex(),
            IntegerField::new('noteSur5', 'Note sur 5')->onlyOnIndex(),
            TextareaField::new('commentaire', 'Commentaire')
                ->onlyOnIndex(), // mets .hideOnIndex() / .onlyOnForms() si tu préfères l’éditer
            DateTimeField::new('createdAt', 'Déposé le')->onlyOnIndex(),

            // On laisse aussi éditable dans le formulaire d’édition
            BooleanField::new('valide', 'Validé')
                ->renderAsSwitch(true),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $approve = Action::new('approve', 'Valider')
            ->setIcon('fa fa-check')
            ->setCssClass('btn btn-success')
            ->displayIf(static fn ($entity) => $entity instanceof Avis && !$entity->isValide())
            ->linkToCrudAction('approve');

        $unapprove = Action::new('unapprove', 'Invalider')
            ->setIcon('fa fa-times')
            ->setCssClass('btn btn-warning')
            ->displayIf(static fn ($entity) => $entity instanceof Avis && $entity->isValide())
            ->linkToCrudAction('unapprove');

        return $actions
            ->disable(Action::NEW)
            // Afficher sur INDEX
            ->add(Crud::PAGE_INDEX, $approve)
            ->add(Crud::PAGE_INDEX, $unapprove)
            // Afficher aussi sur DETAIL
            ->add(Crud::PAGE_DETAIL, $approve)
            ->add(Crud::PAGE_DETAIL, $unapprove);
    }

    /** Met l’avis en “validé = true”. */
    public function approve(AdminContext $context, EntityManagerInterface $em): Response
    {
        $entity = $context->getEntity()->getInstance();
        if (!$entity instanceof Avis) {
            $this->addFlash('danger', 'Avis introuvable.');
            return $this->redirectToIndex();
        }

        if (!$entity->isValide()) {
            $entity->setValide(true);
            $em->flush();
            $this->addFlash('success', 'Avis validé.');
        }

        return $this->redirectToIndex();
    }

    /** Met l’avis en “validé = false”. */
    public function unapprove(AdminContext $context, EntityManagerInterface $em): Response
    {
        $entity = $context->getEntity()->getInstance();
        if (!$entity instanceof Avis) {
            $this->addFlash('danger', 'Avis introuvable.');
            return $this->redirectToIndex();
        }

        if ($entity->isValide()) {
            $entity->setValide(false);
            $em->flush();
            $this->addFlash('success', 'Avis invalidé.');
        }

        return $this->redirectToIndex();
    }

    private function redirectToIndex(): Response
    {
        $url = $this->urlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Cinema;
use App\Entity\Salle;
use App\Entity\Seance;
use App\Repository\SalleRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;

final class SeanceCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SalleRepository $salleRepo,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Seance::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Séance')
            ->setEntityLabelInPlural('Séances')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des séances')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer une séance')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la séance')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail de la séance');
    }

    public function configureAssets(Assets $assets): Assets
    {
        // Assure-toi d’avoir le fichier public/js/admin-seance.js
        return $assets->addJsFile('js/admin-seance.js');
    }

    public function configureFields(string $pageName): iterable
    {
        yield DateField::new('date', '📅 Date')->setFormat('dd/MM/yyyy');
        yield TimeField::new('heureDebut', '🕒 Début')->setFormat('HH:mm');
        yield TimeField::new('heureFin', '🕕 Fin')->setFormat('HH:mm');

        // Pour simplifier le dépendant, on évite l'autocomplete ici
        yield AssociationField::new('cinema', '🎦 Cinéma')
            ->setFormTypeOption('placeholder', '— Choisir un cinéma —');

        // Salle dépendante : on force EntityType pour contrôler les choices côté serveur
        yield AssociationField::new('salle', '🏛️ Salle')
            ->setFormType(EntityType::class)
            ->setFormTypeOptions([
                'class' => Salle::class,
                'choices' => [], // rempli dynamiquement (PRE_SET_DATA / PRE_SUBMIT et JS)
                'placeholder' => '— D’abord choisir un cinéma —',
                'required' => true,
                'choice_label' => fn (?Salle $s) => $s ? (string)$s : '',
                'attr' => [
                    // utilisé par le JS pour charger via AJAX au changement de cinéma
                    'data-endpoint' => '/admin/ajax/salles-by-cinema',
                ],
            ])
            ->setHelp('La liste est limitée aux salles du cinéma sélectionné.');

        // Film : tu peux laisser en autocomplete si tu veux, ça n’impacte pas la logique salle/cinéma
        yield AssociationField::new('film', '🎬 Film')->setFormTypeOption('placeholder', '— Choisir un film —');

        yield TextField::new('qualite', '🎞️ Qualité')
            ->onlyOnIndex()
            ->formatValue(static fn($value, Seance $s) => $s->getQualite()?->getLabel() ?? '—');

            yield MoneyField::new('prix', '💰 Prix')
            ->onlyOnIndex()
            ->setCurrency('EUR')
            ->setNumDecimals(2)
            ->setStoredAsCents(false);

        yield IntegerField::new('placesDisponible', '🎟️ Places restantes')->hideOnForm();

        yield IntegerField::new('nombrePlacesSalle', '🪑 Places totales')
            ->onlyOnIndex()
            ->setHelp('Lecture seule : nombre de sièges de la salle.');
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $builder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        $this->addSalleDependentListeners($builder);
        return $builder;
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $builder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        $this->addSalleDependentListeners($builder);
        return $builder;
    }

    private function addSalleDependentListeners(FormBuilderInterface $builder): void
    {
        // 1) Chargement initial (new/edit)
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Seance|null $seance */
            $seance = $event->getData();
            $form   = $event->getForm();
            $cinema = $seance?->getCinema();
            $this->replaceSalleChoices($form, $cinema, $seance?->getSalle());
        });

        // 2) Rebuild des choices lors de la soumission (changement de cinéma)
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data   = $event->getData() ?? [];
            $form   = $event->getForm();
            $cinema = !empty($data['cinema'])
                ? $this->em->getRepository(Cinema::class)->find($data['cinema'])
                : null;

            $selectedSalle = null;
            if (!empty($data['salle'])) {
                $selectedSalle = $this->em->getRepository(Salle::class)->find($data['salle']);
            }

            $this->replaceSalleChoices($form, $cinema, $selectedSalle);
        });

        // 3) Anti-triche/cohérence: salle ∈ cinéma
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var Seance $seance */
            $seance = $event->getData();
            $form   = $event->getForm();
            $cinema = $seance?->getCinema();
            $salle  = $seance?->getSalle();

            if ($cinema && $salle && $salle->getCinema()?->getId() !== $cinema->getId()) {
                $form->get('salle')->addError(new FormError('Cette salle n’appartient pas au cinéma sélectionné.'));
            }
        });
    }

    /**
     * Remplace dynamiquement les choices du champ "salle" en fonction du cinéma.
     * $currentSalle permet de pré-sélectionner la valeur (édition / retour de validation).
     */
    private function replaceSalleChoices(FormInterface $form, ?Cinema $cinema, ?Salle $currentSalle = null): void
    {
        $choices = [];
        if ($cinema) {
            $choices = $this->salleRepo->createQueryBuilder('s')
                ->andWhere('s.cinema = :c')->setParameter('c', $cinema)
                ->orderBy('s.nom', 'ASC')
                ->getQuery()->getResult();
        }

        $form->add('salle', EntityType::class, [
            'class' => Salle::class,
            'choices' => $choices,
            'placeholder' => $cinema ? '— Sélectionner une salle —' : '— D’abord choisir un cinéma —',
            'required' => true,
            'choice_label' => fn (?Salle $s) => $s ? (string)$s : '',
            'data' => $currentSalle, // conserve la valeur en édition si cohérente
            'attr' => [
                'data-endpoint' => '/admin/ajax/salles-by-cinema',
            ],
        ]);
    }
}

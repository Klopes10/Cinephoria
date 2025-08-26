<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use App\Entity\Siege;
use App\Entity\Cinema;
use App\Entity\Film;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use MongoDB\Client;

class ReservationCrudController extends AbstractCrudController
{
    private Client $mongoClient;
    private EntityManagerInterface $entityManager;

    public function __construct(Client $mongoClient, EntityManagerInterface $entityManager)
    {
        $this->mongoClient = $mongoClient;
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Informations réservation');

        yield AssociationField::new('user', 'Utilisateur');

        // Champ cinéma (non mappé)
        yield ChoiceField::new('cinema', 'Cinéma')
            ->setChoices(fn () => $this->entityManager
                ->getRepository(Cinema::class)
                ->createQueryBuilder('c')
                ->orderBy('c.nom', 'ASC')
                ->getQuery()
                ->getResult()
            )
            ->setFormTypeOption('choice_label', fn (Cinema $cinema) => $cinema->getNom())
            ->setFormTypeOption('choice_value', 'id')
            ->setFormTypeOption('mapped', false)
            ->setFormTypeOption('required', true)
            ->setFormTypeOption('placeholder', 'Choisissez un cinéma')
            ->onlyOnForms();


        // Champ film (non mappé, rempli dynamiquement via JS)
        yield ChoiceField::new('film', 'Film')
            ->setChoices([]) // vide au chargement
            ->setFormType(ChoiceType::class)
            ->setFormTypeOption('mapped', false)
            ->setFormTypeOption('required', true)
            ->setFormTypeOption('placeholder', 'Sélectionnez un film')
            ->onlyOnForms();

        // Champ séance réel
        yield AssociationField::new('seance');

        yield IntegerField::new('nombrePlaces', 'Nombre de places');

        yield CollectionField::new('sieges', 'Sièges')
            ->setEntryType(EntityType::class)
            ->setFormTypeOption('entry_options', [
                'class' => Siege::class,
                'choice_label' => fn(Siege $siege) => $siege->getNumero(),
                'query_builder' => fn($er) => $er->createQueryBuilder('s')
                    ->where('s.isReserved = false'),
            ]);

        yield DateTimeField::new('createdAt', "Réservé le : ")->hideOnForm();

        // Colonnes d'index
        yield TextField::new('user.email', 'Email')->onlyOnIndex();
        yield TextField::new('seance.cinema.nom', 'Cinéma')->onlyOnIndex();
        yield TextField::new('seance.film.titre', 'Film')->onlyOnIndex();
        yield TextField::new('seance', 'Séance')
            ->onlyOnIndex()
            ->formatValue(function ($value, $entity) {
                if (!$entity instanceof Reservation) return '';
                $seance = $entity->getSeance();
                if (!$seance) return '';
                return $seance->getDate()->format('d/m/Y') . ' à ' . $seance->getHeureDebut()->format('H:i');
            });

        yield IntegerField::new('nombrePlaces', 'Places')->onlyOnIndex();
        yield TextField::new('siegesString', 'Sièges')->onlyOnIndex();
        yield MoneyField::new('prixTotal', 'Prix total')->setCurrency('EUR')->onlyOnIndex();
    }

    public function createEntity(string $entityFqcn)
    {
        $reservation = new Reservation();
        $reservation->setCreatedAt(new \DateTimeImmutable());
        return $reservation;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Reservation) return;

        $this->calculerPrixEtPlaces($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);

        $entityManager->refresh($entityInstance);

        $this->enregistrerDansMongo($entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Reservation) return;

        $this->calculerPrixEtPlaces($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function calculerPrixEtPlaces(Reservation $reservation): void
    {
        $seance = $reservation->getSeance();
        $nbPlaces = $reservation->getNombrePlaces();

        if ($seance && $nbPlaces) {
            $reservation->setPrixTotal($seance->getPrix() * $nbPlaces);
        }

        foreach ($reservation->getSieges() as $siege) {
            $siege->setIsReserved(true);
        }
    }

    private function enregistrerDansMongo(Reservation $reservation): void
    {
        $seance = $reservation->getSeance();
        $film = $seance?->getFilm();
        if (!$film) return;

        $today = (new \DateTimeImmutable())->setTime(0, 0);

        $collection = $this->mongoClient
            ->selectDatabase('Cinephoria')
            ->selectCollection('reservations_stats');

        $collection->updateOne(
            [
                'film_titre' => $film->getTitre(),
                'jour' => new \MongoDB\BSON\UTCDateTime($today->getTimestamp() * 1000),
            ],
            [
                '$inc' => ['total_places' => $reservation->getNombrePlaces()]
            ],
            ['upsert' => true]
        );
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW); // autorise Edit et Delete, supprime juste NEW
    }
    
}

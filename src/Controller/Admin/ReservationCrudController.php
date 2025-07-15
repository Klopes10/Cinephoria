<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use App\Entity\Siege;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use MongoDB\Client;

class ReservationCrudController extends AbstractCrudController
{
    private Client $mongoClient;

    public function __construct(Client $mongoClient)
    {
        $this->mongoClient = $mongoClient;
    }

    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('user.username', 'Utilisateur')->onlyOnIndex();
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

        yield AssociationField::new('user')->onlyOnForms();
        yield AssociationField::new('seance')->onlyOnForms();
        yield IntegerField::new('nombrePlaces', 'Nombre de places')->onlyOnForms();
        yield CollectionField::new('sieges', 'Sièges')
            ->setEntryType(EntityType::class)
            ->setFormTypeOption('entry_options', [
                'class' => Siege::class,
                'choice_label' => fn(Siege $siege) => $siege->getNumero(),
                'query_builder' => fn($er) => $er->createQueryBuilder('s')->where('s.isReserved = false'),
            ])
            ->onlyOnForms();

        yield DateTimeField::new('createdAt', "Réservé le : ")->hideOnForm();
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

        // Recharge les relations pour éviter le null sur getFilm()
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

    $today = (new \DateTimeImmutable())->setTime(0, 0); // date du jour sans heure

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
}


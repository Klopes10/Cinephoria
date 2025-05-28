<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;

class ReservationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('nombrePlaces', 'Nombre de places'),
            MoneyField::new('prixTotal', 'Prix total')
                ->setCurrency('EUR')
                ->onlyOnIndex(),
            DateTimeField::new('createdAt')->hideOnForm(),
            AssociationField::new('user'),
            AssociationField::new('seance'),
        ];
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

        $seance = $entityInstance->getSeance();
        $nbPlaces = $entityInstance->getNombrePlaces();

        if ($seance && $nbPlaces) {
            $prixUnitaire = $seance->getPrix();
            $entityInstance->setPrixTotal($prixUnitaire * $nbPlaces);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Reservation) return;

        $seance = $entityInstance->getSeance();
        $nbPlaces = $entityInstance->getNombrePlaces();

        if ($seance && $nbPlaces) {
            $prixUnitaire = $seance->getPrix();
            $entityInstance->setPrixTotal($prixUnitaire * $nbPlaces);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}

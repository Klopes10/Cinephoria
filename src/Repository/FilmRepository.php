<?php

namespace App\Repository;

use App\Entity\Film;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Film>
 */
class FilmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Film::class);
    }

    /**
     * Retourne les films ayant une séance à venir,
     * en filtrant par ville, genre, et jour si précisé.
     */
    public function findFiltered(?string $ville, ?string $genre, ?string $jour): array
    {
        $qb = $this->createQueryBuilder('f')
            ->distinct()
            ->leftJoin('f.genre', 'g')
            ->leftJoin('f.seances', 's')
            ->leftJoin('s.salle', 'sa')
            ->leftJoin('sa.cinema', 'c');

        if ($ville) {
            $qb->andWhere('c.ville = :ville')
               ->setParameter('ville', $ville);
        }

        if ($genre) {
            $qb->andWhere('g.nom = :genre')
               ->setParameter('genre', $genre);
        }

        // Ne montrer que les séances à venir
        $now = new \DateTimeImmutable();
        $qb->andWhere('s.date >= :now')
           ->setParameter('now', $now);

        // Filtrage par jour spécifique
        if ($jour) {
            $joursMap = [
                'Lundi' => 1,
                'Mardi' => 2,
                'Mercredi' => 3,
                'Jeudi' => 4,
                'Vendredi' => 5,
                'Samedi' => 6,
                'Dimanche' => 0,
            ];

            if (isset($joursMap[$jour])) {
                $qb->andWhere('EXTRACT(DOW FROM s.date) = :day')
                   ->setParameter('day', $joursMap[$jour]);
            }
        }

        // Sécurité : on limite à 20 résultats max pour éviter surcharge
        $qb->setMaxResults(20);

        return $qb->getQuery()->getResult();
    }
}

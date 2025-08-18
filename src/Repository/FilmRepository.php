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


    public function findFilmsWithSessionsInCityOnDate(
        string $ville,
        string $pays,
        \DateTimeInterface $date,
        ?int $filmId = null
    ): array {
        // Jointures: Film f -> Seance s -> Salle sa -> Cinema c
        // s.date est un champ DATE (sinon adapte)
        $conn = $this->getEntityManager()->getConnection();
    
        $sql = '
            SELECT
                f.id   AS film_id,
                f.titre AS film_titre,
                f.affiche AS film_affiche,
                f.genres  AS film_genres,
                f.age_minimum AS film_age,
                f.synopsis AS film_synopsis,
    
                s.id    AS seance_id,
                TO_CHAR(s.heure_debut, \'HH24:MI\') AS heure_debut,
                sa.nom  AS salle_nom,
                s.places_disponible AS places_disponible
            FROM film f
            INNER JOIN seance s   ON s.film_id = f.id
            INNER JOIN salle sa   ON s.salle_id = sa.id
            INNER JOIN cinema c   ON sa.cinema_id = c.id
            WHERE c.ville = :ville
              AND c.pays  = :pays
              AND s.date  = :jour
        ';
    
        $params = [
            'ville' => $ville,
            'pays'  => $pays,
            'jour'  => $date->format('Y-m-d'),
        ];
    
        if ($filmId) {
            $sql .= ' AND f.id = :fid';
            $params['fid'] = $filmId;
        }
    
        $sql .= ' ORDER BY f.titre ASC, s.heure_debut ASC';
    
        return $conn->executeQuery($sql, $params)->fetchAllAssociative();
    }

}

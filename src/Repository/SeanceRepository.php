<?php

namespace App\Repository;

use App\Entity\Film;
use App\Entity\Seance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SeanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seance::class);
    }

    /**
     * Concatène date (Y-m-d) + heure (H:i:s) en DateTimeImmutable.
     */
    private function mergeDateAndTime(\DateTimeImmutable $date, \DateTimeImmutable $time): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $date->format('Y-m-d') . ' ' . $time->format('H:i:s'),
            $date->getTimezone()
        );
    }

    /**
     * Toutes les séances à venir d’un film, sans filtrer ville/jour.
     * Triées par ville, date, heure_debut.
     */
    public function findUpcomingForFilm(Film $film): array
    {
        $today = new \DateTimeImmutable('today');

        $qb = $this->createQueryBuilder('s')
            ->innerJoin('s.film', 'f')
            ->innerJoin('s.cinema', 'c')
            ->innerJoin('s.salle', 'sa')
            ->addSelect('f', 'c', 'sa')
            ->andWhere('f = :film')
            ->andWhere('s.date >= :today')
            ->setParameter('film', $film)
            ->setParameter('today', $today)
            ->orderBy('c.ville', 'ASC')
            ->addOrderBy('s.date', 'ASC')
            ->addOrderBy('s.heureDebut', 'ASC');

        $list = $qb->getQuery()->getResult();

        $out = [];
        foreach ($list as $seance) {
            /** @var Seance $seance */
            $start = $this->mergeDateAndTime($seance->getDate(), $seance->getHeureDebut());
            $end   = $this->mergeDateAndTime($seance->getDate(), $seance->getHeureFin());

            $out[] = [
                'id'      => $seance->getId(),
                'date'    => $start,                    // début (pour afficher H:i)
                'fin'     => $end,                      // fin (pour "fin à H:i")
                'ville'   => $seance->getCinema()->getVille(),
                'cinema'  => method_exists($seance->getCinema(), 'getNom') ? $seance->getCinema()->getNom() : null,
                'salle'   => method_exists($seance->getSalle(), 'getNumero') ? $seance->getSalle()->getNumero() : null,
                'format'  => $seance->getQualite(),     // ta colonne "qualite" (IMAX, 4DX, etc.)
                'version' => null,                      // ajoute si tu as un champ version
            ];
        }

        return $out;
        // NB: le groupement (ville -> date(Y-m-d) -> seances) est fait dans le contrôleur comme tu l’as déjà.
    }

    /**
     * Séances d’un film pour une ville précise et un jour (YYYY-MM-DD).
     */
    public function findByFilmVilleAndDate(Film $film, string $ville, string $dateYmd): array
    {
        $dateObj = \DateTimeImmutable::createFromFormat('Y-m-d', $dateYmd) ?: new \DateTimeImmutable('today');

        $qb = $this->createQueryBuilder('s')
            ->innerJoin('s.film', 'f')
            ->innerJoin('s.cinema', 'c')
            ->innerJoin('s.salle', 'sa')
            ->addSelect('f', 'c', 'sa')
            ->andWhere('f = :film')
            ->andWhere('LOWER(c.ville) = LOWER(:ville)')
            ->andWhere('s.date = :d')
            ->setParameter('film', $film)
            ->setParameter('ville', $ville)
            ->setParameter('d', $dateObj)
            ->orderBy('s.heureDebut', 'ASC');

        $list = $qb->getQuery()->getResult();

        $out = [];
        foreach ($list as $seance) {
            /** @var Seance $seance */
            $start = $this->mergeDateAndTime($seance->getDate(), $seance->getHeureDebut());
            $end   = $this->mergeDateAndTime($seance->getDate(), $seance->getHeureFin());

            $out[] = [
                'id'      => $seance->getId(),
                'date'    => $start,
                'fin'     => $end,
                'ville'   => $seance->getCinema()->getVille(),
                'cinema'  => method_exists($seance->getCinema(), 'getNom') ? $seance->getCinema()->getNom() : null,
                'salle'   => method_exists($seance->getSalle(), 'getNumero') ? $seance->getSalle()->getNumero() : null,
                'format'  => $seance->getQualite(),
                'version' => null,
            ];
        }

        return $out;
    }

    public function countByFilmVilleBetweenDates(
        Film $film,
        string $ville,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->select('s.date AS d, COUNT(s.id) AS nb')
            ->innerJoin('s.film', 'f')
            ->innerJoin('s.cinema', 'c')
            ->andWhere('f = :film')
            ->andWhere('LOWER(c.ville) = LOWER(:ville)')
            ->andWhere('s.date BETWEEN :start AND :end')
            ->groupBy('s.date')
            ->orderBy('s.date', 'ASC')
            ->setParameter('film', $film)
            ->setParameter('ville', $ville)
            ->setParameter('start', $start)
            ->setParameter('end', $end);
    
        $rows = $qb->getQuery()->getScalarResult();
    
        // Retourne un map: 'Y-m-d' => count
        $out = [];
        foreach ($rows as $r) {
            $key = is_string($r['d'])
                ? $r['d']
                : ($r['d'] instanceof \DateTimeInterface ? $r['d']->format('Y-m-d') : (string)$r['d']);
            $out[$key] = (int) $r['nb'];
        }
        return $out;
    }
    
}

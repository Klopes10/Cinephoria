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
     * Séances d’un film pour une ville précise et un jour (YYYY-MM-DD).
     * Retourne une liste de tableaux prêts pour Twig :
     *  [
     *    'id' => int,
     *    'date' => DateTimeInterface (début),
     *    'fin'  => DateTimeInterface (fin),
     *    'ville' => string,
     *    'cinema' => ?string,
     *    'salle' => string|int|null,  // nom ou numéro selon ton entité
     *    'format' => ?string,         // ex: IMAX, 4DX...
     *    'version' => ?string
     *  ]
     */
    public function findByFilmVilleAndDate(Film $film, string $ville, string $dateYmd): array
    {
        $dateObj = \DateTimeImmutable::createFromFormat('Y-m-d', $dateYmd)
            ?: new \DateTimeImmutable('today');

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

        /** @var Seance[] $list */
        $list = $qb->getQuery()->getResult();

        $out = [];
        foreach ($list as $seance) {
            $start = $this->mergeDateAndTime($seance->getDate(), $seance->getHeureDebut());
            $end   = $this->mergeDateAndTime($seance->getDate(), $seance->getHeureFin());

            // salle: renvoyer un nom si disponible, sinon un numéro, sinon null
            $salleLabel = null;
            if ($seance->getSalle()) {
                if (method_exists($seance->getSalle(), 'getNom') && $seance->getSalle()->getNom()) {
                    $salleLabel = $seance->getSalle()->getNom();
                } elseif (method_exists($seance->getSalle(), 'getNumero')) {
                    $salleLabel = $seance->getSalle()->getNumero();
                }
            }

            $out[] = [
                'id'      => $seance->getId(),
                'date'    => $start,
                'fin'     => $end,
                'ville'   => $seance->getCinema()->getVille(),
                'cinema'  => method_exists($seance->getCinema(), 'getNom') ? $seance->getCinema()->getNom() : null,
                'salle'   => $salleLabel,
                'format'  => $seance->getQualite(), // ex: IMAX, 4DX...
                'version' => null,                  // adapte si tu as un champ "version"
            ];
        }

        return $out;
    }

    /**
     * Compte des séances par jour (sur une fenêtre de dates) pour un film et une ville.
     * Retour: ['Y-m-d' => count, ...]
     */
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

        $out = [];
        foreach ($rows as $r) {
            // Doctrine peut renvoyer un DateTimeImmutable ou une string selon le driver/mapping
            $key = $r['d'] instanceof \DateTimeInterface ? $r['d']->format('Y-m-d') : (string) $r['d'];
            $out[$key] = (int) $r['nb'];
        }
        return $out;
    }

    /**
     * Préchargement pour interaction instantanée (JS).
     * Renvoie une map prête à JSON: [ville][Y-m-d] = list de créneaux,
     * chaque créneau: { id, time, end, format, version, salle } (tout en string/int).
     */
    public function findByFilmBetweenDatesForJs(
        Film $film,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->innerJoin('s.film', 'f')
            ->innerJoin('s.cinema', 'c')
            ->innerJoin('s.salle', 'sa')
            ->addSelect('f', 'c', 'sa')
            ->andWhere('f = :film')
            ->andWhere('s.date BETWEEN :start AND :end')
            ->orderBy('c.ville', 'ASC')
            ->addOrderBy('s.date', 'ASC')
            ->addOrderBy('s.heureDebut', 'ASC')
            ->setParameter('film', $film)
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        /** @var Seance[] $list */
        $list = $qb->getQuery()->getResult();

        $out = []; // [ville][Y-m-d] => [ {id,time,end,format,version,salle} ... ]

        foreach ($list as $seance) {
            $ville = $seance->getCinema()->getVille();
            $ymd   = $seance->getDate()->format('Y-m-d');

            $startDT = $this->mergeDateAndTime($seance->getDate(), $seance->getHeureDebut());
            $endDT   = $this->mergeDateAndTime($seance->getDate(), $seance->getHeureFin());

            // Libellé salle
            $salleLabel = null;
            if ($seance->getSalle()) {
                if (method_exists($seance->getSalle(), 'getNom') && $seance->getSalle()->getNom()) {
                    $salleLabel = $seance->getSalle()->getNom();
                } elseif (method_exists($seance->getSalle(), 'getNumero')) {
                    $salleLabel = $seance->getSalle()->getNumero();
                }
            }

            $out[$ville][$ymd][] = [
                'id'      => $seance->getId(),
                'time'    => $startDT->format('H:i'),
                'end'     => $endDT->format('H:i'),
                'format'  => strtolower((string) $seance->getQualite()),
                'version' => null, // adapte si tu as un champ
                'salle'   => $salleLabel,
            ];
        }

        return $out;
    }

    /**
     * (Optionnel) Toutes les séances à venir d’un film, non utilisées directement ici
     * mais utile pour d’autres écrans.
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

        /** @var Seance[] $list */
        $list = $qb->getQuery()->getResult();

        $out = [];
        foreach ($list as $seance) {
            $start = $this->mergeDateAndTime($seance->getDate(), $seance->getHeureDebut());
            $end   = $this->mergeDateAndTime($seance->getDate(), $seance->getHeureFin());

            $salleLabel = null;
            if ($seance->getSalle()) {
                if (method_exists($seance->getSalle(), 'getNom') && $seance->getSalle()->getNom()) {
                    $salleLabel = $seance->getSalle()->getNom();
                } elseif (method_exists($seance->getSalle(), 'getNumero')) {
                    $salleLabel = $seance->getSalle()->getNumero();
                }
            }

            $out[] = [
                'id'      => $seance->getId(),
                'date'    => $start,
                'fin'     => $end,
                'ville'   => $seance->getCinema()->getVille(),
                'cinema'  => method_exists($seance->getCinema(), 'getNom') ? $seance->getCinema()->getNom() : null,
                'salle'   => $salleLabel,
                'format'  => $seance->getQualite(),
                'version' => null,
            ];
        }

        return $out;
    }

    
// pour la page reservation
public function findForCityDate(
    string $ville,
    string $pays,
    \DateTimeInterface $date,
    ?int $filmId = null
): array {
    $qb = $this->createQueryBuilder('s')
        ->innerJoin('s.film', 'f')->addSelect('f')
        ->innerJoin('s.salle', 'sa')->addSelect('sa')
        ->innerJoin('sa.cinema', 'c')->addSelect('c')
        ->andWhere('c.ville = :ville')
        ->andWhere('c.pays  = :pays')
        ->andWhere('s.date  = :jour')
        ->setParameter('ville', $ville)
        ->setParameter('pays',  $pays)
        // IMPORTANT : on passe l'objet DateTime + type DATE_IMMUTABLE (adapter si ton mapping diffère)
        ->setParameter('jour',  $date, Types::DATE_IMMUTABLE)
        ->orderBy('f.titre', 'ASC')
        ->addOrderBy('s.heureDebut', 'ASC');

    if ($filmId !== null) {
        $qb->andWhere('f.id = :fid')->setParameter('fid', $filmId);
    }

    return $qb->getQuery()->getResult();
}
}

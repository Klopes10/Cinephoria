<?php
namespace App\Repository;

use App\Entity\Avis;
use App\Entity\Film;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    /** Moyenne des notes (avis validés) sur 5, ex: 3.8 */
    public function getAverageNoteForFilm(Film $film): ?float
    {
        $avg = $this->createQueryBuilder('a')
            ->select('AVG(a.noteSur5) AS avgNote')
            ->andWhere('a.film = :film')
            ->andWhere('a.valide = true')
            ->setParameter('film', $film)
            ->getQuery()->getSingleScalarResult();

        return $avg !== null ? round((float)$avg, 1) : null;
    }

    /** Nombre d'avis validés pour un film */
    public function countValidatedForFilm(Film $film): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.film = :film')
            ->andWhere('a.valide = true')
            ->setParameter('film', $film)
            ->getQuery()->getSingleScalarResult();
    }

    /** Liste des avis validés (avec l'utilisateur), du plus récent au plus ancien */
    public function findValidatedForFilm(Film $film): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.user', 'u')->addSelect('u')
            ->andWhere('a.film = :film')
            ->andWhere('a.valide = true')
            ->setParameter('film', $film)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()->getResult();
    }

    public function getNotesByUserForFilmIds(User $user, array $filmIds): array
    {
        if (!$filmIds) {
            return [];
        }

        $rows = $this->createQueryBuilder('a')
            ->select('IDENTITY(a.film) AS film_id, a.noteSur5 AS note')
            ->where('a.user = :u')
            ->andWhere('a.valide = true')
            ->andWhere('a.film IN (:ids)')
            ->setParameter('u', $user)
            ->setParameter('ids', $filmIds)
            ->getQuery()
            ->getArrayResult();

        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['film_id']] = (int)$r['note'];
        }
        return $map;
    }
}

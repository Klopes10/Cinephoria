<?php

namespace App\Repository;


use App\Entity\Avis;
use App\Entity\Film;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Avis>
 */
class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    public function getAverageNoteForFilm(Film $film): ?float
{
    $avg = $this->createQueryBuilder('a')
        ->select('AVG(a.noteSur5) AS avgNote')   // champ entité: noteSur5
        ->andWhere('a.film = :film')
        ->setParameter('film', $film)
        ->getQuery()
        ->getSingleScalarResult();

    return $avg !== null ? round((float)$avg, 1) : null; // ex: 3.7
}
}

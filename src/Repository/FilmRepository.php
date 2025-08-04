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

   public function findFilmsFiltered(?string $ville, ?string $genre, ?string $jour): array
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
        $qb->andWhere('g.id = :genre')
           ->setParameter('genre', $genre);
    }

    if ($jour) {
        $date = \DateTime::createFromFormat('Y-m-d', $jour);
        if ($date) {
            $start = (clone $date)->setTime(0, 0, 0);
            $end = (clone $date)->setTime(23, 59, 59);
            $qb->andWhere('s.date BETWEEN :start AND :end')
               ->setParameter('start', $start)
               ->setParameter('end', $end);
        }
    }

    return $qb->getQuery()->getResult();
}
}
<?php
namespace App\Repository;

use App\Entity\Cinema;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CinemaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cinema::class);
    }

    /** @return string[] */
    public function findAllDistinctCities(): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select('DISTINCT c.ville AS ville')
            ->orderBy('c.ville', 'ASC')
            ->getQuery()->getScalarResult();

        return array_map(static fn(array $r) => $r['ville'], $rows);
    }

    /**
     * Retourne un tableau simple de villes distinctes pour un pays donné.
     * Exemple: "France" ou "Belgique" (champ Cinema.pays).
     *
     * @return string[]
     */
    public function findDistinctCitiesByCountry(string $pays): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('DISTINCT c.ville AS ville')
            ->andWhere('LOWER(c.pays) = LOWER(:pays)')
            ->setParameter('pays', $pays)
            ->orderBy('c.ville', 'ASC');

        // getScalarResult() => [ ['ville'=>'Lyon'], ['ville'=>'Paris'] ... ]
        $rows = $qb->getQuery()->getScalarResult();

        return array_map(static fn(array $r) => $r['ville'], $rows);
    }
}

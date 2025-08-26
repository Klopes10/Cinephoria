<?php

namespace App\Repository;

use App\Entity\Qualite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Qualite>
 *
 * @method Qualite|null find($id, $lockMode = null, $lockVersion = null)
 * @method Qualite|null findOneBy(array $criteria, array $orderBy = null)
 * @method Qualite[]    findAll()
 * @method Qualite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QualiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Qualite::class);
    }

    public function save(Qualite $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Qualite $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Liste triée par label (pour des dropdowns, etc.)
     */
    public function findAllOrderedByLabel(): array
    {
        return $this->createQueryBuilder('q')
            ->orderBy('q.label', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

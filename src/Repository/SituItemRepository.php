<?php

namespace App\Repository;

use App\Entity\SituItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SituItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method SituItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method SituItem[]    findAll()
 * @method SituItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SituItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SituItem::class);
    }

    // /**
    //  * @return SituItem[] Returns an array of SituItem objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SituItem
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

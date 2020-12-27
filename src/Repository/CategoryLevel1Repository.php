<?php

namespace App\Repository;

use App\Entity\CategoryLevel1;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CategoryLevel1|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryLevel1|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryLevel1[]    findAll()
 * @method CategoryLevel1[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryLevel1Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryLevel1::class);
    }

    // /**
    //  * @return CategoryLevel1[] Returns an array of CategoryLevel1 objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CategoryLevel1
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

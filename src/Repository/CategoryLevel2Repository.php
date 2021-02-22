<?php

namespace App\Repository;

use App\Entity\CategoryLevel2;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CategoryLevel2|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryLevel2|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryLevel2[]    findAll()
 * @method CategoryLevel2[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryLevel2Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryLevel2::class);
    }
    
    public function findCategoriesByCategoryLevel1($categoryLevel1Id)
    {        
        return $this->createQueryBuilder('c')
                    ->andWhere("c.categoryLevel1Id = ?1")
                    ->andWhere("c.validated = ?2")
                    ->setParameter(1, $categoryLevel1Id)
                    ->setParameter(2, 1)
                    ->select('c.id, c.title, c.description')
                    ->getQuery()
                    ->getResult();
    }

    // /**
    //  * @return CategoryLevel2[] Returns an array of CategoryLevel2 objects
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
    public function findOneBySomeField($value): ?CategoryLevel2
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

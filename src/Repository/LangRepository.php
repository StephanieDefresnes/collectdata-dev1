<?php

namespace App\Repository;

use App\Entity\Lang;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Lang|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lang|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lang[]    findAll()
 * @method Lang[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LangRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lang::class);
    }

    // /**
    //  * @return Lang[] Returns an array of Lang objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Lang
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

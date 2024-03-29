<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Category::class);
        $this->security = $security;
    }
        
    /**
     * @return []   Returns an array of Categories Level1 objects
     *              by event selected and user event category not yet validated
     * 
     * @param type $eventId
     * @param type $langId
     */
    public function findByEventAndUser($eventId)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $eventCategories = $qb->expr()->andX(
            $qb->expr()->eq('c.event', '?1'),
            $qb->expr()->eq('c.validated', '?2')
        );
        
        $eventCategoriesByUser = $qb->expr()->andX(
            $qb->expr()->eq('c.event', '?1'),
            $qb->expr()->eq('c.validated', '?3'),
            $qb->expr()->eq('c.user', '?4')
        );
        
        $qb->from(Category::class,'c')
            ->select('c')
            ->andWhere($qb->expr()->orX($eventCategories, $eventCategoriesByUser))
            ->setParameters([
                1 => $eventId,
                2 => true,
                3 => false,
                4 => $this->security->getUser()->getId()
            ]);
        
        return $qb->getQuery()->getResult();
    }
        
    /**
     * @return []   Returns an array of Categories Level2 objects
     *              by category selected and user parent category not yet validated
     * 
     * @param type $categoryId
     * @param type $langId
     */
    public function findByParentAndUser($categoryId)
    {        
        $qb = $this->_em->createQueryBuilder();
        
        $parentCategories = $qb->expr()->andX(
            $qb->expr()->eq('c.parent', '?1'),
            $qb->expr()->eq('c.validated', '?2')
        );
        
        $parentCategoriesByUser = $qb->expr()->andX(
            $qb->expr()->eq('c.parent', '?1'),
            $qb->expr()->eq('c.validated', '?3'),
            $qb->expr()->eq('c.user', '?4')
        );
        
        $qb->from(Category::class,'c')
            ->select('c')
            ->andWhere($qb->expr()->orX($parentCategories, $parentCategoriesByUser))
            ->setParameters([
                1 => $categoryId,
                2 => true,
                3 => false,
                4 => $this->security->getUser()->getId()
            ]);
        return $qb->getQuery()->getResult();
    }

    // /**
    //  * @return Category[] Returns an array of Category objects
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
    public function findOneBySomeField($value): ?Category
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

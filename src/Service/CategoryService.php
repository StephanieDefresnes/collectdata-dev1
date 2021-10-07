<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Lang;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class CategoryService {

    private $em;
    
    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }
        
    /**
     * @return []   Returns categoryLevel1 land id
     *              Use to get not validated Categories Level1 added by current user
     */
    public function getLangByCategoryId($categoryId)
    {
        return $this->em->createQueryBuilder()
                ->from(Category::class,'c')
                ->select('l.id landId')
                ->leftJoin(Lang::class, 'l', 'WITH', 'c.lang = l.id')
                ->where('c.id = ?1')
                ->setParameter(1, $categoryId)
                ->getQuery()
                ->getOneOrNullResult();
    }
        
    /**
     * @return []   Returns an array of Categories Level1 objects
     *              by event selected and by user categories
     */
    public function getByEventIdAndbyUserEvent($eventId, $langId)
    {
        $qb = $this->em->createQueryBuilder();
        
        $categoryLevel1ByEventId = $qb->expr()->andX(
            $qb->expr()->eq('c.event', '?1'),
            $qb->expr()->eq('c.validated', '?2')
        );
        
        $categoryLevel1ByUser = $qb->expr()->andX(
            $qb->expr()->eq('c.event', '?1'),
            $qb->expr()->eq('c.validated', '?3'),
            $qb->expr()->eq('c.userId', '?4'),
            $qb->expr()->eq('c.lang', '?5')
        );
        
        $qb->from(Category::class,'c')
            ->select('c')
            ->andWhere($qb->expr()->orX($categoryLevel1ByEventId, $categoryLevel1ByUser))
            ->setParameters([
                1 => $eventId,
                2 => 1,
                3 => 0,
                4 => $this->security->getUser()->getId(),
                5 => $langId
            ]);
        
        return $qb->getQuery()->getResult();
    }
        
    /**
     * @return []   Returns an array of Categories Level2 objects
     *              by Category parent selected and by user categories
     */
    public function getByParentIdAndUserParentId($categoryId, $langId)
    {        
        $qb = $this->em->createQueryBuilder();
        
        $categoriesByByParentId = $qb->expr()->andX(
            $qb->expr()->eq('c.parent', '?1'),
            $qb->expr()->eq('c.validated', '?2')
        );
        
        $categoriesByUserParentId = $qb->expr()->andX(
            $qb->expr()->eq('c.parent', '?1'),
            $qb->expr()->eq('c.validated', '?3'),
            $qb->expr()->eq('c.userId', '?4'),
            $qb->expr()->eq('c.lang', '?5')
        );
        
        $qb->from(Category::class,'c')
                ->select('c')
                ->andWhere($qb->expr()->orX($categoriesByByParentId, $categoriesByUserParentId))
                ->setParameters([
                    1 => $categoryId,
                    2 => 1,
                    3 => 0,
                    4 => $this->security->getUser()->getId(),
                    5 => $langId
                ]);
        return $qb->getQuery()->getResult();
    }
        
    /**
     * @return []   Returns Category description
     */
    public function getDescriptionById($categoryId)
    {
        return $this->em->createQueryBuilder()
                ->from(Category::class,'c')
                ->select('c.description')
                ->where('c.id = ?1')
                ->setParameter(1, $categoryId)
                ->getQuery()
                ->getOneOrNullResult();
    }
    
    public function getCategory($categoryId) {
        return $this->em->getRepository(Category::class)->find($categoryId);
    }
}

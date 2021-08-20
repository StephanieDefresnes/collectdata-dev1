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
    public function getCategoryLangById($category_id)
    {
        return $this->em->createQueryBuilder()
                ->from(Category::class,'c')
                ->select('l.id landId')
                ->leftJoin(Lang::class, 'l', 'WITH', 'c.lang = l.id')
                ->where('c.id = ?1')
                ->setParameter(1, $category_id)
                ->getQuery()
                ->getOneOrNullResult();
    }
        
    /**
     * @return []   Returns an array of Categories Level1 objects
     *              by event selected and by user categories
     */
    public function getByEventAndbyUserEvent($event_id, $lang_id)
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
                1 => $event_id,
                2 => 1,
                3 => 0,
                4 => $this->security->getUser()->getId(),
                5 => $lang_id
            ]);
        
        return $qb->getQuery()->getResult();
    }
        
    /**
     * @return []   Returns an array of Categories Level2 objects
     *              by Category parent selected and by user categories
     */
    public function getByLevel1AndUserLevel1($category_id, $lang_id)
    {        
        $qb = $this->em->createQueryBuilder();
        
        $categoriesByCategoryId = $qb->expr()->andX(
            $qb->expr()->eq('c.parent', '?1'),
            $qb->expr()->eq('c.validated', '?2')
        );
        
        $categoriesByUser = $qb->expr()->andX(
            $qb->expr()->eq('c.parent', '?1'),
            $qb->expr()->eq('c.validated', '?3'),
            $qb->expr()->eq('c.userId', '?4'),
            $qb->expr()->eq('c.lang', '?5')
        );
        
        $qb->from(Category::class,'c')
                ->select('c')
                ->andWhere($qb->expr()->orX($categoriesByCategoryId, $categoriesByUser))
                ->setParameters([
                    1 => $category_id,
                    2 => 1,
                    3 => 0,
                    4 => $this->security->getUser()->getId(),
                    5 => $lang_id
                ]);
        return $qb->getQuery()->getResult();
    }
        
    /**
     * @return []   Returns Category data description & validated
     */
    public function getDataById($category_id)
    {
        return $this->em->createQueryBuilder()
                ->from(Category::class,'c')
                ->select('c.title, c.description, c.validated')
                ->where('c.id = ?1')
                ->setParameter(1, $category_id)
                ->getQuery()
                ->getOneOrNullResult();
    }
}

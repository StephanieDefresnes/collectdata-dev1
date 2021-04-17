<?php

namespace App\Service;

use App\Entity\CategoryLevel2;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class CategoryLevel2Service {

    private $em;
    
    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }
        
    /**
     * @return []   Returns an array of Categories Level2 objects
     *              by CategoryLevel1 selected and by user categories
     */
    public function getValidatedAndByEventUser($catLv1_id, $lang_id)
    {        
        $qb = $this->em->createQueryBuilder();
        
        $catLv2ByCatLv1Id = $qb->expr()->andX(
            $qb->expr()->eq('c.categoryLevel1', '?1'),
            $qb->expr()->eq('c.validated', '?2')
        );
        
        $catLv2ByUser = $qb->expr()->andX(
            $qb->expr()->eq('c.categoryLevel1', '?1'),
            $qb->expr()->eq('c.validated', '?3'),
            $qb->expr()->eq('c.userId', '?4'),
            $qb->expr()->eq('c.lang', '?5')
        );
        
        $qb->from(CategoryLevel2::class,'c')
                ->select('c')
                ->andWhere($qb->expr()->orX($catLv2ByCatLv1Id, $catLv2ByUser))
                ->setParameters([
                    1 => $catLv1_id,
                    2 => 1,
                    3 => 0,
                    4 => $this->security->getUser()->getId(),
                    5 => $lang_id
                ]);
        return $qb->getQuery()->getResult();
    }
    
}

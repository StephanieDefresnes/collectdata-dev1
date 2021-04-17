<?php

namespace App\Service;

use App\Entity\CategoryLevel1;
use App\Entity\Lang;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class CategoryLevel1Service {

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
    public function getCatLv1LangById($catLv1_id)
    {
        return $this->em->createQueryBuilder()
                ->from(CategoryLevel1::class,'evt')
                ->select('lang.id landId')
                ->leftJoin(Lang::class, 'lang', 'WITH', 'evt.lang = lang.id')
                ->where('evt.id = ?1')
                ->setParameter(1, $catLv1_id)
                ->getQuery()
                ->getOneOrNullResult();
    }
        
    /**
     * @return []   Returns an array of Categories Level1 objects
     *              by event selected and by user categories
     */
    public function getByEventAndByEventUser($event_id, $event_lang)
    {
        $qb = $this->em->createQueryBuilder();
        
        $catLv1ByEventId = $qb->expr()->andX(
            $qb->expr()->eq('c.event', '?1'),
            $qb->expr()->eq('c.validated', '?2')
        );
        
        $catLv1ByUser = $qb->expr()->andX(
            $qb->expr()->eq('c.event', '?1'),
            $qb->expr()->eq('c.validated', '?3'),
            $qb->expr()->eq('c.userId', '?4'),
            $qb->expr()->eq('c.lang', '?5')
        );
        
        $qb->from(CategoryLevel1::class,'c')
            ->select('c')
            ->andWhere($qb->expr()->orX($catLv1ByEventId, $catLv1ByUser))
            ->setParameters([
                1 => $event_id,
                2 => 1,
                3 => 0,
                4 => $this->security->getUser()->getId(),
                5 => $event_lang
            ]);
        return $qb->getQuery()->getResult();
    }
    
}

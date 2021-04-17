<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Lang;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class EventService {

    private $em;
    
    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }
        
    /**
     * @return []   Returns event land id
     *              Use to get not validated Events added by current user
     */
    public function getEventLangById($event_id)
    {
        return $this->em->createQueryBuilder()
                ->from(Event::class,'evt')
                ->select('lang.id landId')
                ->leftJoin(Lang::class, 'lang', 'WITH', 'evt.lang = lang.id')
                ->where('evt.id = ?1')
                ->setParameter(1, $event_id)
                ->getQuery()
                ->getOneOrNullResult();
    }
        
    /**
     * @return []   Returns an array of Events objects
     *              by lang selected and by user events
     */
    public function getByLangAndByUserLang($lang_id)
    {
        $qb = $this->em->createQueryBuilder();
        
        $eventByLang = $qb->expr()->andX(
            $qb->expr()->eq('c.lang', '?1'),
            $qb->expr()->eq('c.validated', '?2')
        );
        
        $eventByUser = $qb->expr()->andX(
            $qb->expr()->eq('c.lang', '?1'),
            $qb->expr()->eq('c.validated', '?3'),
            $qb->expr()->eq('c.userId', '?4')
        );
        
        $qb->from(Event::class,'c')
            ->select('c')
            ->andWhere($qb->expr()->orX($eventByLang, $eventByUser))
            ->setParameters([
                1 => $lang_id,
                2 => 1,
                3 => 0,
                4 => $this->security->getUser()->getId(),
            ]);
        return $qb->getQuery()->getResult();
    }
    
}

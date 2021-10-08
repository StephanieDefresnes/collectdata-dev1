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
     *              Use to get not validated Events yet and added by current user
     */
    public function getEventLang($eventId)
    {
        return $this->em->createQueryBuilder()
                ->from(Event::class,'e')
                ->select('l.id landId')
                ->leftJoin(Lang::class, 'l', 'WITH', 'e.lang = l.id')
                ->where('e.id = ?1')
                ->setParameter(1, $eventId)
                ->getQuery()
                ->getOneOrNullResult();
    }
        
    /**
     * @return []   Returns an array of Events objects
     *              by lang selected and by user events not validated yet
     */
    public function getByLangIdAndUserLangId($langId)
    {
        $qb = $this->em->createQueryBuilder();
        
        $eventByLangId = $qb->expr()->andX(
            $qb->expr()->eq('e.lang', '?1'),
            $qb->expr()->eq('e.validated', '?2')
        );
        
        $eventByUserLandId = $qb->expr()->andX(
            $qb->expr()->eq('e.lang', '?1'),
            $qb->expr()->eq('e.validated', '?3'),
            $qb->expr()->eq('e.userId', '?4')
        );
        
        $qb->from(Event::class,'e')
            ->select('e')
            ->andWhere($qb->expr()->orX($eventByLangId, $eventByUserLandId))
            ->setParameters([
                1 => $langId,
                2 => 1,
                3 => 0,
                4 => $this->security->getUser()->getId(),
            ]);
        return $qb->getQuery()->getResult();
    }
        
    /**
     * @return []   Returns Category data validated
     */
    public function getDataById($eventId)
    {
        return $this->em->createQueryBuilder()
                ->from(Event::class,'e')
                ->select('e.title, e.validated')
                ->where('e.id = ?1')
                ->setParameter(1, $event_id)
                ->getQuery()
                ->getOneOrNullResult();
    }
    
    /**
     * Called in twig
     */
    public function getEvent($eventId) {
        return $this->em->getRepository(Event::class)->find($eventId);
    }
    
}

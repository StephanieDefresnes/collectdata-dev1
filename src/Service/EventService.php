<?php

namespace App\Service;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class EventService {

    private $em;
    
    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
    }
    
    /* === Called in twig === */
    
    /**
     * back\message\alerts.html.twig
     * 
     * @param type $eventId
     * @return type
     */
    public function getEvent($eventId) {
        return $this->em->getRepository(Event::class)->find($eventId);
    }
    
}

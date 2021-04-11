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
        $this->security = $security;
    }
        
    /**
     * @return []   Returns an array of Events objects
     *              by lang selected and by user events
     */
    public function getByLangAndByCategoryUser()
    {
        $userLangId = $this->security->getUser()->getLangId();
        if ($userLangId == '') {
            $userLangId = 47;
        }
        $userId = $this->security->getUser()->getId();
        
        $query = $this->em->createQueryBuilder();
        $query->from(Event::class,'e')
                ->select(  'e.id id')
                ->andWhere('e.lang = ?1')
                ->andWhere('e.validated = ?2')
                ->andWhere($query->expr()->orX(
                    $query->expr()->eq('e.userId ', '?3'),
                    $query->expr()->eq('e.validated', '?4')
                ))
                ->setParameter(1, $userLangId)
                ->setParameter(2, 1)
                ->setParameter(3, $userId)
                ->setParameter(4, 0);
        return $query->getQuery()->getResult();
    }
    
}

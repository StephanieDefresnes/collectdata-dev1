<?php

namespace App\Service;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;

class MessageService {

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function getUnreadUserAlerts($userId) {
        
        return $this->em->createQueryBuilder()
            ->from(Message::class,'m')
            ->select('m')
            ->andWhere('m.scanned = ?1')
            ->andWhere('m.recipientUserId = ?2')
            ->setParameter(1, 0)
            ->setParameter(2, $userId)
            ->addOrderBy('m.dateCreate', 'DESC')
            ->getQuery()->getResult();
    }
    
    public function getUser($userId) {
        
    }
}
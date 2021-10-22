<?php

namespace App\Service;

use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;

class StatusService {

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function getUsualStatus()
    {
        $qb = $this->em->createQueryBuilder();        
        $qb->from(Status::class,'s')
                ->select('s')
                ->where($qb->expr()->neq('s.id', '?1'))
                ->setParameters([
                    1 => '-1',
                ]);
        
        return $qb->getQuery()->getResult();
    }
    
}
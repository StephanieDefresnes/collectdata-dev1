<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Situ;
use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;

class SituValidator {
    
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function situValidation($data)
    {
        $situ = $this->em->getRepository(Situ::class)->find($data['id']);
        
        $situ->setStatus($this->em->getRepository(Status::class)->find($data['statusId']));
        
        if ($data['action'] === 'validation') {
            
            $situ->setDateValidation(new \DateTime('now'));
            
            if ($data['eventValidated'] === '1') {
                $event = $this->em->getRepository(Event::class)->find($data['eventId']);
                if ($event->getValidated() === false) $event->setValidated(true);
            }
            if ($data['categoryLevel1Validated'] === '1') {
                $categoryLevel1 = $this->em->getRepository(Category::class)
                                        ->find($data['categoryLevel1Id']);
                if ($categoryLevel1->getValidated() === false) {
                    $categoryLevel1->setValidated(true);
                }
            }
            if ($data['categoryLevel2Validated'] === '1') {
                $categoryLevel2 = $this->em->getRepository(Category::class)
                                        ->find($data['categoryLevel2Id']);
                if ($categoryLevel2->getValidated() === false) {
                    $categoryLevel2->setValidated(true);
                }
            }
            
            // notification validation (message)
            
        } else {
            
            $comment = $data['comment'];
            
            // notification refuse (message)
        }
            
        try {            
            $this->em->flush();
            
            $result = ['success' => true];
            
        } catch (\Doctrine\DBAL\DBALException $e) {
            $result = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }
        
        return $result;
    }
    
}

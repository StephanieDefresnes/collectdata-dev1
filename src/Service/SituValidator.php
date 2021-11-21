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
            
            if ($this->checkValidation('event', $data['eventId'], $data['eventValidated']) === 'validated') {
                // todo notification (alert)
            }
            if ($this->checkValidation('categoryLevel1', $data['categoryLevel1Id'], $data['categoryLevel1Validated']) === 'validated') {
                // todo notification (alert)
            }
            if ($this->checkValidation('categoryLevel2', $data['categoryLevel2Id'], $data['categoryLevel2Validated']) === 'validated') {
                // todo notification (alert)
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
    
    public function checkValidation($entity, $id, $validated)
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        
        if ($entity === 'event') $class = Event::class;
        else $class = Category::class;
        
        $classId = $this->em->getRepository($class)->find($id);
        
        if ($classId->getValidated() === false && $validated === 1) {
            $classId->setValidated(true);
            
            try {
                $this->em->flush();                
                return 'validated';
                
            } catch (\Doctrine\DBAL\DBALException $e) {
                return $e->getMessage();
            }
        }
    }
    
}

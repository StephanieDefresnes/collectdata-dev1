<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Situ;
use App\Entity\Status;
use App\Mailer\Mailer;
use App\Messager\Messager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class SituValidator {
    
    private $em;
    private $mailer;
    private $messager;
    
    public function __construct(EntityManagerInterface $em,
                                Mailer $mailer,
                                Messager $messager,
                                Security $security)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->messager = $messager;
        $this->security = $security;
    }
    
    public function situValidation($data)
    {
        $situ = $this->em->getRepository(Situ::class)->find($data['id']);
        
        $situ->setStatus($this->em->getRepository(Status::class)->find($data['statusId']));
        
        $entities = [];
        $user = $this->security->getUser();
        
        if ($data['action'] === 'validation') {
            
            $situ->setDateValidation(new \DateTime('now'));
            $this->em->persist($situ);
            
            // SUPER_VISITOR can't validate his own event neither category
            if (!$user->hasRole('ROLE_SUPER_VISITOR')) {
                
                if ($data['eventValidated'] === '1') {
                    $event = $this->em->getRepository(Event::class)->find($data['eventId']);
                    if ($event->getValidated() === false) {
                        $event->setValidated(true);
                        $entities['event'] = $event;
                    }
                    $this->em->persist($event);
                }
                if ($data['categoryLevel1Validated'] === '1') {
                    $categoryLevel1 = $this->em->getRepository(Category::class)
                                            ->find($data['categoryLevel1Id']);
                    if ($categoryLevel1->getValidated() === false) {
                        $categoryLevel1->setValidated(true);
                        $entities['categoryLevel1'] = $categoryLevel1;
                    }
                    $this->em->persist($categoryLevel1);
                }
                if ($data['categoryLevel2Validated'] === '1') {
                    $categoryLevel2 = $this->em->getRepository(Category::class)
                                            ->find($data['categoryLevel2Id']);
                    if ($categoryLevel2->getValidated() === false) {
                        $categoryLevel2->setValidated(true);
                        $entities['categoryLevel2'] = $categoryLevel2;
                    }
                    $this->em->persist($categoryLevel2);
                }
            }
            
            // notification validation (message)
            $entities['situ'] = ['validation' => true];
            
        } elseif ($data['action'] === 'refuse') {
            $situ->setDateValidation(null);
            
            $comment = $data['comment'];
            
            $entities['situ'] = [
                'validation' => false,
                'situ' => $situ,
                'text' => $comment,
            ];
        }
            
        try {
            
            // Filter super visitor, can fush only his owns
            if ($user->hasRole('ROLE_SUPER_VISITOR')
                    && $situ->getUser() !== $user) {
                $result = [
                    'success' => true,
                    'validator' => false,
                ];
                
            } else {
            
                $this->em->flush();

                if (array_key_exists('event', $entities)) {
                    $this->messager->sendUserAlert('validation', 'event', $event);
                }

                if (array_key_exists('categoryLevel1', $entities)) {
                    $this->messager->sendUserAlert('validation', 'categoryLevel1', $categoryLevel1);
                }

                if (array_key_exists('categoryLevel2', $entities)) {
                    $this->messager->sendUserAlert('validation', 'categoryLevel2', $categoryLevel2);
                }

                if (true === $entities['situ']['validation']) {
                    $this->messager->sendUserAlert('validation', 'situ', $situ);
                    $this->mailer->sendUserSituValidation($situ, $situ->getUser());
                } else {
                    $message = $this->messager
                                ->sendUserEnvelope('situ_refuse', $entities['situ']['text'], $situ);
                    $this->mailer->sendUserMessage($message['message'], $situ->getUser());
                }

                $result = [
                    'success' => true,
                    'validator' => true,
                ];
            }
            
        } catch (\Doctrine\DBAL\DBALException $e) {
            $result = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }
        
        return $result;
    }
    
}
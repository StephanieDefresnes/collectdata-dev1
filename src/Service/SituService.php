<?php

namespace App\Service;

use App\Entity\Situ;
use App\Entity\Event;
use App\Entity\CategoryLevel1;
use App\Entity\CategoryLevel2;
use App\Entity\Status;
use App\Entity\Lang;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;

class SituService {

    private $em;
    private $eventRepository;
    
    public function __construct(EntityManagerInterface $em,
                                EventRepository $eventRepository)
    {
        $this->em = $em;
        $this->eventRepository = $eventRepository;
    }

    public function getSitusByUser($userId) 
    {           
        $query = $this->em->createQueryBuilder()
            ->from(Situ::class,'situ')
            ->select(  'situ.id id,
                        situ.title title, 
                        situ.description description, 
                        situ.dateCreation dateCreation, 
                        situ.dateLastUpdate dateLastUpdate, 
                        situ.dateSubmission dateSubmission, 
                        situ.dateValidation dateValidation, 
                        situ.statusId statusId,
                        evt.id eventId, 
                        evt.title eventTitle, 
                        cat1.id cat1Id, 
                        cat1.title cat1Title, 
                        cat2.id cat2Id, 
                        cat2.title cat2Title, 
                        status.name statusName, 
                        lang.name langName')
            ->leftJoin(Event::class, 'evt', 'WITH', 'situ.event=evt.id')
            ->leftJoin(CategoryLevel1::class, 'cat1', 'WITH', 'situ.categoryLevel1=cat1.id')
            ->leftJoin(CategoryLevel2::class, 'cat2', 'WITH', 'situ.categoryLevel2=cat2.id')
            ->leftJoin(Status::class, 'status', 'WITH', 'situ.statusId=status.id')
            ->leftJoin(Lang::class, 'lang', 'WITH', 'situ.langId=lang.id')
            ->where("situ.userId = '$userId' ");
        
        $situs = $query->getQuery()->getResult();
        $result = [];
        foreach ($situs as $situ) {
            $result[] = [ 
                'id' => $situ['id'],
                'title' => $situ['title'],
                'description' => $situ['description'],
                'dateCreation' => $situ['dateCreation'],
                'dateLastUpdate' => $situ['dateLastUpdate'],
                'dateSubmission' => $situ['dateSubmission'],
                'dateValidation' => $situ['dateValidation'],
                'statusId' => $situ['statusId'],
                'evtId' => $situ['eventId'],
                'evtTitle' => $situ['eventTitle'],
                'cat1Id' => $situ['cat1Id'],
                'cat1Title' => $situ['cat1Title'],
                'cat2Id' => $situ['cat2Id'],
                'cat2Title' => $situ['cat2Title'],
                'statusName' => $situ['statusName'],
                'langName' => html_entity_decode($situ['langName'], ENT_QUOTES, 'UTF-8'),
            ];
        }
        return $result;
        
    }
}
<?php

namespace App\Service;

use App\Entity\Situ;
use App\Entity\SituItem;
use App\Entity\Event;
use App\Entity\Category;
use App\Entity\Status;
use App\Entity\Lang;
use Doctrine\ORM\EntityManagerInterface;

class SituService {

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getSitusByUser($userId) 
    {   
        $query = $this->em->createQueryBuilder()
            ->from(Situ::class,'situ')
            ->select(  'situ.id                 id,
                        situ.title              title,
                        situ.description        description,
                        situ.dateCreation       dateCreation,
                        situ.dateLastUpdate     dateLastUpdate,
                        situ.dateSubmission     dateSubmission,
                        situ.dateValidation     dateValidation,
                        situ.statusId           statusId,
                        situ.translatedSituId   translatedSituId,
                        situ.userId             userId,
                        evt.id                  eventId,
                        evt.title               eventTitle,
                        cat1.id                 cat1Id,
                        cat1.title              cat1Title,
                        cat2.id                 cat2Id,
                        cat2.title              cat2Title,
                        lang.name               langName')
            ->leftJoin(Event::class, 'evt', 'WITH', 'situ.event=evt.id')
            ->leftJoin(Category::class, 'cat1', 'WITH', 'situ.categoryLevel1=cat1.id')
            ->leftJoin(Category::class, 'cat2', 'WITH', 'situ.categoryLevel2=cat2.id')
            ->leftJoin(Lang::class, 'lang', 'WITH', 'situ.lang=lang.id')
            ->where("situ.userId = '$userId'")
            ->orderBy('situ.id', 'DESC');
        
        $situs = $query->getQuery()->getResult();
        $result = [];
        foreach ($situs as $situ) {
            $result[] = [ 
                'id' =>                 $situ['id'],
                'title' =>              $situ['title'],
                'description' =>        $situ['description'],
                'dateCreation' =>       $situ['dateCreation'],
                'dateLastUpdate' =>     $situ['dateLastUpdate'],
                'dateSubmission' =>     $situ['dateSubmission'],
                'dateValidation' =>     $situ['dateValidation'],
                'statusId' =>           $situ['statusId'],
                'translatedSituId' =>   $situ['translatedSituId'],
                'userId' =>             $situ['userId'],
                'evtId' =>              $situ['eventId'],
                'evtTitle' =>           $situ['eventTitle'],
                'cat1Id' =>             $situ['cat1Id'],
                'cat1Title' =>          $situ['cat1Title'],
                'cat2Id' =>             $situ['cat2Id'],
                'cat2Title' =>          $situ['cat2Title'],
                'langName' =>           html_entity_decode($situ['langName'], ENT_QUOTES, 'UTF-8'),
            ];
        }
        return $result;
        
    }

    public function getSituById($situId) 
    {   
        $query = $this->em->createQueryBuilder()
            ->from(Situ::class,'situ')
            ->select(  'situ.id                 id,
                        situ.title              title, 
                        situ.description        description,
                        situ.userId             userId, 
                        evt.id                  eventId, 
                        cat1.id                 categoryLevel1Id, 
                        cat2.id                 categoryLevel2Id,
                        lang.id                 langId')
            ->leftJoin(Event::class, 'evt', 'WITH', 'situ.event=evt.id')
            ->leftJoin(Category::class, 'cat1', 'WITH', 'situ.categoryLevel1=cat1.id')
            ->leftJoin(Category::class, 'cat2', 'WITH', 'situ.categoryLevel2=cat2.id')
            ->leftJoin(Lang::class, 'lang', 'WITH', 'situ.lang=lang.id')
            ->andWhere('situ.id = ?1')
            ->setParameter(1, $situId);
        
        return $query->getQuery()->getOneOrNullResult();
    }
    
    public function getSituItemsBySituId($situId)
    {
        $query = $this->em->createQueryBuilder()
            ->from(SituItem::class,'item')
            ->select('  item.id             id,
                        item.title          title, 
                        item.description    description,
                        item.score          score')
            ->andWhere('item.situ = ?1')
            ->setParameter(1, $situId)
            ->orderBy('item.score', 'ASC');
        
        $message = $query->getQuery()->getResult();
        return $message;
    }
    
    public function countSitusByUser($userId)
    {
        $query = $this->em->createQueryBuilder()
            ->from(Situ::class,'situ')
            ->andWhere("situ.userId = ?1")
            ->andWhere("situ.statusId = ?2")
            ->setParameter(1, $userId)
            ->setParameter(2, 3)
            ->select('count(situ.id)');
        
        return $situs = $query->getQuery()->getSingleScalarResult();
    }
    
    public function countSitusByLangByUser($userId)
    {
        $query = $this->em->createQueryBuilder()
            ->from(Situ::class,'situ')
            ->select(  'count(situ.id)          situs, 
                        situ.translatedSituId   translatedSituId,
                        lang.name               langName')
            ->leftJoin(Lang::class, 'lang', 'WITH', 'situ.lang=lang.id')
            ->groupBy('lang.id')
            ->andWhere("situ.userId = ?1")
            ->andWhere("situ.statusId = ?2")
            ->setParameter(1, $userId)
            ->setParameter(2, 3);
        
        $situs = $situs = $query->getQuery()->getScalarResult();
        $result = [];
        foreach ($situs as $situ) {
            $result[] = [ 
                'situs' => $situ['situs'],
                'translatedSituId' => $situ['translatedSituId'],
                'langName' => html_entity_decode($situ['langName'], ENT_QUOTES, 'UTF-8'),
            ];
        }
        
        return $result;
    }
    
    public function countSitusTranslatedByLangByUser($userId)
    {
        $query = $this->em->createQueryBuilder()
            ->from(Situ::class,'situ')
            ->select(   'count(situ.id)     situs, 
                        lang.name           langName')
            ->leftJoin(Lang::class, 'lang', 'WITH', 'situ.lang=lang.id')
            ->groupBy('lang.id')
            ->andWhere("situ.userId = ?1")
            ->andWhere("situ.statusId = ?2")
            ->andWhere("situ.translatedSituId IS NOT NULL")
            ->setParameter(1, $userId)
            ->setParameter(2, 3);
        
        $situs = $situs = $query->getQuery()->getScalarResult();
        $result = [];
        foreach ($situs as $situ) {
            $result[] = [ 
                'situs' => $situ['situs'],
                'langName' => html_entity_decode($situ['langName'], ENT_QUOTES, 'UTF-8'),
            ];
        }
        
        return $result;
    }
    
}
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
    
    public function countSitusByLangByUser($userId)
    {
        $query = $this->em->createQueryBuilder()
            ->from(Situ::class,'situ')
            ->select('  count(situ.id)          situs, 
                        situ.translatedSituId   translatedSituId,
                        lang.name               langName')
            ->leftJoin(Lang::class, 'lang', 'WITH', 'situ.lang=lang.id')
            ->groupBy('lang.id')
            ->andWhere('situ.user = ?1')
            ->andWhere('situ.statusId = ?2')
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
    
    public function searchTranslation($situId, $langId)
    {
        $qb = $this->em->createQueryBuilder();        
        $qb->from(Situ::class,'situ')
            ->select('situ.id')
            ->where('situ.translatedSituId = ?1')
            ->andWhere('situ.lang = ?2')
            ->andWhere($qb->expr()->neq('situ.statusId', '?3'))
            ->andWhere($qb->expr()->neq('situ.statusId', '?4'))
            ->setParameters([
                1 => $situId,
                2 => $langId,
                3 => 4,
                4 => 5
            ]);
        
        return $qb->getQuery()->getResult();
    }
    
    /**
     * Called in twig
     */
    // Get status on alerts
    public function getSitu($situId) {
        return $this->em->getRepository(Situ::class)->find($situId);
    }
    // Get translations read situ
    public function getTranslations($situId) {
        return $this->em->getRepository(Situ::class)
                ->findby(['translatedSituId' => $situId]);
    }
    
}
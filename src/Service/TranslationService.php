<?php

namespace App\Service;

use App\Entity\Translation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TranslationService {

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function getTranslationsForms()
    {
        $query = $this->em->createQueryBuilder()
            ->from(Translation::class,'t')
            ->select('  t.id                id,
                        t.name              name,
                        t.lang              lang,
                        t.statusId          statusId,
                        t.dateCreation      dateCreation,
                        t.dateLastUpdate    dateLastUpdate,
                        t.dateStatus        dateStatus,
                        t.enabled           enabled,    
                        t.userId            userId,
                        user.name           userName')
            ->leftJoin(User::class, 'user', 'WITH', 't.userId=user.id')
            ->andWhere('t.referent = ?1')
            ->andWhere('t.statusId != ?2')
            ->setParameter(1, 1)
            ->setParameter(2, 5)
            ->addOrderBy('t.dateCreation', 'DESC')
            ->addOrderBy('t.dateStatus', 'DESC');
        $translations = $query->getQuery()->getResult();
        
        $result = [];
        foreach ($translations as $t) {
            $result[] = [
                'id' =>             $t['id'],
                'name' =>           $t['name'],
                'lang' =>           $t['lang'],
                'statusId' =>       $t['statusId'],
                'dateCreation' =>   $t['dateCreation'],
                'dateLastUpdate' => $t['dateLastUpdate'],
                'dateStatus' =>     $t['dateStatus'],
                'enabled' =>        $t['enabled'],
                'userId' =>         $t['userId'],
                'userName' =>       $t['userName'],
            ];
        }
        return $result;
    }
    
    public function getTranslations($langs)
    {
        $query = $this->em->createQueryBuilder()
            ->from(Translation::class,'t')
            ->select('  t.id                id,
                        t.name              name,
                        t.referent          referent,
                        t.referentId        referentId,
                        t.lang              lang,
                        t.langId            langId,
                        t.statusId          statusId,
                        t.dateCreation      dateCreation,
                        t.dateLastUpdate    dateLastUpdate,
                        t.dateStatus        dateStatus,
                        t.enabled           enabled,
                        t.userId            userId,
                        user.name           userName')
            ->leftJoin(User::class, 'user', 'WITH', 't.userId=user.id')
            ->andWhere('t.referent = ?1')
            ->andWhere('t.lang IN (:langs)')
            ->setParameter(1, 0)
            ->setParameter('langs', $langs)
            ->addOrderBy('t.name', 'ASC');
        $translations = $query->getQuery()->getResult();
        
        $result = [];
        foreach ($translations as $t) {
            $result[] = [
                'id' =>             $t['id'],
                'name' =>           $t['name'],
                'referent' =>       $t['referent'],
                'referentId' =>     $t['referentId'],
                'lang' =>           $t['lang'],
                'langId' =>         $t['langId'],
                'statusId' =>       $t['statusId'],
                'dateCreation' =>   $t['dateCreation'],
                'dateLastUpdate' => $t['dateLastUpdate'],
                'dateStatus' =>     $t['dateStatus'],
                'enabled' =>        $t['enabled'],
                'userId' =>         $t['userId'],
                'userName' =>       $t['userName'],
            ];
        }
        return $result;
    }
    
    public function getUserTranslation($userId, $referentId, $langId) {
        
        return $this->em->createQueryBuilder()
            ->from(Translation::class,'t')
            ->select('  t.id                id,
                        t.referentId        referentId,
                        t.langId            langId,
                        t.statusId          statusId')
            ->andWhere('t.userId = ?1')
            ->andWhere('t.referentId = ?2')
            ->andWhere('t.langId = ?3')
            ->setParameter(1, $userId)
            ->setParameter(2, $referentId)
            ->setParameter(3, $langId)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
}

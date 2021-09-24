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
    
    public function getAllTranslationsReferent()
    {
        $query = $this->em->createQueryBuilder()
            ->from(Translation::class,'translation')
            ->select('  translation.id              id,
                        translation.name            name,
                        translation.lang            lang,
                        translation.statusId        statusId,
                        translation.dateCreation    dateCreation,
                        translation.dateLastUpdate  dateLastUpdate,
                        translation.dateStatus      dateStatus,
                        translation.userId          userId,
                        user.name                   userName')
            ->leftJoin(User::class, 'user', 'WITH', 'translation.userId=user.id')
            ->andWhere('translation.referent = ?1')
            ->setParameter(1, 1)
            ->addOrderBy('translation.dateCreation', 'DESC')
            ->addOrderBy('translation.dateStatus', 'DESC');
        
        $translations = $query->getQuery()->getResult();
        $result = [];
        foreach ($translations as $translation) {
            $result[] = [
                'id' =>             $translation['id'],
                'name' =>           $translation['name'],
                'lang' =>           $translation['lang'],
                'statusId' =>       $translation['statusId'],
                'dateCreation' =>   $translation['dateCreation'],
                'dateLastUpdate' => $translation['dateLastUpdate'],
                'dateStatus' =>     $translation['dateStatus'],
                'userId' =>         $translation['userId'],
                'userName' =>       $translation['userName'],
            ];
        }
        return $result;
    }
    
}

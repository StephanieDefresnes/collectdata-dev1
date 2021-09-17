<?php

namespace App\Service;

use App\Entity\TranslationField;
use App\Entity\Translation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class TranslationService {

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function getFieldsByTranslationId($translation)
    {
        $query = $this->em->createQueryBuilder()
            ->from(TranslationField::class,'field')
            ->select('  field.id    id,
                        field.name  name,
                        field.type  type')
            ->andWhere('field.translation = ?1')
            ->setParameter(1, $translation)
            ->orderBy('field.sorting', 'ASC');
        
        $translation = $query->getQuery()->getResult();
        return $translation;
    }
    
    public function getTranslationById($translation_id)
    {
        $query = $this->em->createQueryBuilder()
            ->from(Translation::class,'translation')
            ->select('  translation.id              id,
                        translation.name            name,
                        translation.userId          userId,
                        translation.statusId        statusId,
                        translation.dateLastUpdate  dateLastUpdate')
            ->andWhere('translation.id = ?1')
            ->setParameter(1, $translation_id);
        
        $translation = $query->getQuery()->getResult();
        return $translation;
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
                        user.name               userName')
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
    
    public function getTranslationsByStatusId($statusId)
    {
        $query = $this->em->createQueryBuilder()
            ->from(Translation::class,'translation')
            ->select('  translation.id              id,
                        translation.name            name,
                        translation.lang            lang,
                        translation.statusId        statusId,
                        translation.dateCreation    dateCreation,
                        translation.dateStatus      dateStatus,
                        translation.userId          userId')
            ->andWhere('translation.referent = ?1')
            ->andWhere('translation.statusId = ?2')
            ->setParameter(1, 1)
            ->setParameter(2, $statusId)
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
                'dateStatus' =>     $translation['dateStatus'],
                'userId' =>         $translation['userId'],
            ];
        }
        return $result;
    }
    
    public function getUserTranslations($userId)
    {
        $query = $this->em->createQueryBuilder()
            ->from(Translation::class,'translation')
            ->select('  translation.id              id,
                        translation.lang            lang,
                        translation.userId          userId')
            ->andWhere('translation.referent = ?1')
            ->andWhere('translation.userId = ?2')
            ->setParameter(1, 0)
            ->setParameter(2, $userId)
            ->addOrderBy('translation.name', 'DESC');
        
        $translations = $query->getQuery()->getResult();
        $result = [];
        foreach ($translations as $translation) {
            $result[] = [
                'id' =>             $translation['id'],
                'lang' =>           $translation['lang'],
                'userId' =>         $translation['userId'],
            ];
        }
        return $result;
    }
    
}

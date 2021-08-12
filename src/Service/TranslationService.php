<?php

namespace App\Service;

use App\Entity\TranslationField;
use App\Entity\TranslationMessage;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class TranslationService {

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function getFieldsByMessageId($message)
    {
        $query = $this->em->createQueryBuilder()
            ->from(TranslationField::class,'field')
            ->select('  field.id    id,
                        field.name  name,
                        field.type  type')
            ->andWhere('field.message = ?1')
            ->setParameter(1, $message)
            ->orderBy('field.sorting', 'ASC');
        
        $message = $query->getQuery()->getResult();
        return $message;
    }
    
    public function getTranslationById($translation_id)
    {
        $query = $this->em->createQueryBuilder()
            ->from(TranslationMessage::class,'message')
            ->select('  message.id              id,
                        message.name            name,
                        message.userId          userId,
                        message.statusId        statusId,
                        message.dateLastUpdate  dateLastUpdate')
            ->andWhere('message.id = ?1')
            ->setParameter(1, $translation_id);
        
        $message = $query->getQuery()->getResult();
        return $message;
    }
    
    public function getAllMessagesReferent()
    {
        $query = $this->em->createQueryBuilder()
            ->from(TranslationMessage::class,'message')
            ->select('  message.id              id,
                        message.name            name,
                        message.lang            lang,
                        message.statusId        statusId,
                        message.dateCreation    dateCreation,
                        message.dateLastUpdate  dateLastUpdate,
                        message.dateStatus      dateStatus,
                        message.userId          userId,
                        user.name               userName')
            ->leftJoin(User::class, 'user', 'WITH', 'message.userId=user.id')
            ->andWhere('message.referent = ?1')
            ->setParameter(1, 1)
            ->addOrderBy('message.dateCreation', 'DESC')
            ->addOrderBy('message.dateStatus', 'DESC');
        
        $messages = $query->getQuery()->getResult();
        $result = [];
        foreach ($messages as $message) {
            $result[] = [
                'id' =>             $message['id'],
                'name' =>           $message['name'],
                'lang' =>           $message['lang'],
                'statusId' =>       $message['statusId'],
                'dateCreation' =>   $message['dateCreation'],
                'dateLastUpdate' => $message['dateLastUpdate'],
                'dateStatus' =>     $message['dateStatus'],
                'userId' =>         $message['userId'],
                'userName' =>       $message['userName'],
            ];
        }
        return $result;
    }
    
    public function getMessagesByStatusId($statusId)
    {
        $query = $this->em->createQueryBuilder()
            ->from(TranslationMessage::class,'message')
            ->select('  message.id              id,
                        message.name            name,
                        message.lang            lang,
                        message.statusId        statusId,
                        message.dateCreation    dateCreation,
                        message.dateStatus      dateStatus,
                        message.userId          userId')
            ->andWhere('message.referent = ?1')
            ->andWhere('message.statusId = ?2')
            ->setParameter(1, 1)
            ->setParameter(2, $statusId)
            ->addOrderBy('message.dateStatus', 'DESC');
        
        $messages = $query->getQuery()->getResult();
        $result = [];
        foreach ($messages as $message) {
            $result[] = [
                'id' =>             $message['id'],
                'name' =>           $message['name'],
                'lang' =>           $message['lang'],
                'statusId' =>       $message['statusId'],
                'dateCreation' =>   $message['dateCreation'],
                'dateStatus' =>     $message['dateStatus'],
                'userId' =>         $message['userId'],
            ];
        }
        return $result;
    }
    
    public function getUserMessages($userId)
    {
        $query = $this->em->createQueryBuilder()
            ->from(TranslationMessage::class,'message')
            ->select('  message.id              id,
                        message.lang            lang,
                        message.userId          userId')
            ->andWhere('message.referent = ?1')
            ->andWhere('message.userId = ?2')
            ->setParameter(1, 0)
            ->setParameter(2, $userId)
            ->addOrderBy('message.name', 'DESC');
        
        $messages = $query->getQuery()->getResult();
        $result = [];
        foreach ($messages as $message) {
            $result[] = [
                'id' =>             $message['id'],
                'lang' =>           $message['lang'],
                'userId' =>         $message['userId'],
            ];
        }
        return $result;
    }
    
}

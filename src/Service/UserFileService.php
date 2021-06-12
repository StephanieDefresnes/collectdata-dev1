<?php

namespace App\Service;

use App\Entity\UserFile;
use App\Entity\Lang;
use Doctrine\ORM\EntityManagerInterface;

class UserFileService {

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function getTransationFilesByLangByUser($userId, $langId)
    {        
        $query = $this->em->createQueryBuilder()
            ->from(UserFile::class,'file')
            ->select(  'file.id             id,
                        file.statusId       statusId,
                        file.filename       filename,
                        file.dateCreation   dateCreation,
                        file.dateValidation dateValidation,
                        lang.id             langId,
                        lang.name           langName')
            ->leftJoin(Lang::class, 'lang', 'WITH', 'file.lang=lang.id')
            ->andWhere("file.user = ?1")
            ->andWhere("file.lang = ?2")
            ->andWhere("file.type = ?3")
            ->setParameter(1, $userId)
            ->setParameter(2, $langId)
            ->setParameter(3, 'translation');
        
        $files = $query->getQuery()->getResult();
        $result = [];
        foreach ($files as $file) {
            $result[] = [ 
                'id' =>             $file['id'],
                'statusId' =>       $file['statusId'],
                'filename' =>       $file['filename'],
                'dateCreation' =>   $file['dateCreation'],
                'dateValidation' => $file['dateValidation'],
                'langId' =>         $file['langId'],
                'langName' =>       html_entity_decode($file['langName'], ENT_QUOTES, 'UTF-8'),
            ];
        }
        return $result;        
    }
    
}

<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Lang;
use Doctrine\ORM\EntityManagerInterface;

class UserService {

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function getUsers()
    {
        $users = $this->em->createQueryBuilder()
            ->from(User::class,'u')
            ->select('  u.id                id,
                        u.email             email,
                        u.roles             roles,
                        u.enabled           enabled,
                        u.name              name,
                        u.dateCreate        dateCreate,
                        u.dateLastLogin     dateLastLogin,
                        u.dateUpdate        dateUpdate,
                        u.adminNote         adminNote,
                        u.langContributor   langContributor,
                        u.imageFilename     imageFilename,
                        l.name              langName')
            ->leftJoin(Lang::class, 'l', 'WITH', 'u.langId=l.id')
            ->getQuery()
            ->getResult();
        
        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id' =>                 $user['id'],
                'email' =>              $user['email'],
                'roles' =>              $user['roles'],
                'enabled' =>            $user['enabled'],
                'name' =>               $user['name'],
                'dateCreate' =>         $user['dateCreate'],
                'dateLastLogin ' =>     $user['dateLastLogin'],
                'dateUpdate ' =>        $user['dateUpdate'],
                'adminNote ' =>         $user['adminNote'],
                'langContributor' =>    $user['langContributor'],
                'imageFilename ' =>     $user['imageFilename'],
                'langName' =>           html_entity_decode($user['langName'], ENT_QUOTES, 'UTF-8'),
            ];
        }
        return $users;
    }
    
    public function getRole($role)
    {
        $qb = $this->em->createQueryBuilder();
        
        $qb->from(User::class,'u')
            ->select('u')
            ->andWhere($qb->expr()->like('u.roles', '?1'))
            ->setParameter(1, '%'.$role.'%');
        
        return $qb->getQuery()->getResult();
    }
    
    public function getRoleByLang($role, $lang)
    {
        $qb = $this->em->createQueryBuilder();
        
        $qb->from(User::class,'u')
            ->select('u')
            ->andWhere($qb->expr()->like('u.roles', '?1'))
            ->andWhere('?2 MEMBER OF u.langs')
            ->setParameter(1, '%'.$role.'%')
            ->setParameter(2, $lang);
        
        return $qb->getQuery()->getResult();
    }    
}

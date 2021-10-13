<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService {

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    /**
     * @return user array expect negative #id (anonymous)
     */
    public function getUsers()
    {
        $query = $this->em->createQueryBuilder()
            ->from(User::class,'u')
            ->select('u')
            ->andWhere('u.id > ?1')
            ->setParameter(1, 0);
        
        return $query->getQuery()->getResult();
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

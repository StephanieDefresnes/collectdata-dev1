<?php

namespace App\Service;

use App\Entity\CategoryLevel2;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class CategoryLevel2Service {

    private $em;
    
    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }
        
    /**
     * @return []   Returns an array of Categories Level2 objects
     *              by CategoryLevel1 selected and by user categories
     */
    public function getValidatedAndByEventUser($catLv1_id)
    {        
        $query = $this->em->createQueryBuilder();
        $query->from(CategoryLevel2::class,'c')
                ->select(  'c.id id')
                ->andWhere('c.categoryLevel1 = ?1')
                ->andWhere('c.validated = ?2')
                ->andWhere($query->expr()->orX(
                    $query->expr()->eq('c.userId ', '?3'),
                    $query->expr()->eq('c.validated', '?4')
                ))
                ->setParameter(1, $catLv1_id)
                ->setParameter(2, 1)
                ->setParameter(3, $this->security->getUser()->getId())
                ->setParameter(4, 0);
        return $query->getQuery()->getResult();
    }
    
}

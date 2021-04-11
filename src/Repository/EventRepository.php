<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Event::class);
        
        $this->security = $security;
    }
    
    /**
     * @return []   Returns an array of Events objects
     *              by locale and by user events
     */
    public function findByLocaleLang()
    {        
        $userId = $this->security->getUser()->getId();
        $userLangId = $this->security->getUser()->getLangId();
        if ($userLangId == '') {
            $userLangId = 47;
        }
        
        $query =  $this->createQueryBuilder('c');
                    $query->andWhere('c.lang = ?1')
                    ->andWhere('c.validated = ?2') 
                    ->andWhere($query->expr()->orX(
                        $query->expr()->eq('c.userId ', '?3'),
                        $query->expr()->eq('c.validated', '?4')
                    ))
                    ->setParameter(1, $userLangId)
                    ->setParameter(2, 1)
                    ->setParameter(3, $userId)
                    ->setParameter(4, 0)
                    ->select('c.id');
        return $query->getQuery()->getResult();        
    }

    // /**
    //  * @return Event[] Returns an array of Event objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

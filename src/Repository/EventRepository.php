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
     *              by lang selected and user events not validated yet
     * 
     * @param type $langId
     * @return type
     */
    public function findByLangAndUser($langId)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $eventsLang = $qb->expr()->andX(
            $qb->expr()->eq('e.lang', '?1'),
            $qb->expr()->eq('e.validated', '?2')
        );
        
       $eventsLangByUser = $qb->expr()->andX(
            $qb->expr()->eq('e.lang', '?1'),
            $qb->expr()->eq('e.validated', '?3'),
            $qb->expr()->eq('e.user', '?4')
        );
        
        $qb->from(Event::class,'e')
            ->select('e')
            ->andWhere($qb->expr()->orX($eventsLang, $eventsLangByUser))
            ->setParameters([
                1 => $langId,
                2 => true,
                3 => false,
                4 => $this->security->getUser()->getId(),
            ]);
        return $qb->getQuery()->getResult();
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

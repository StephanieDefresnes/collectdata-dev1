<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Lang;
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
        $user = $this->security->getUser();
        
        $default = $this->em->getRepository(Lang::class)->findOneBy(
            ['englishName' => 'French']
        );
        
        $userLangId = $user->getLangId() == '' ? $default->getId() : $user->getLangId();
        
        $qb =  $this->createQueryBuilder('c');
        
        $eventByLang = $qb->expr()->andX(
            $qb->expr()->eq('c.lang', '?1'),
            $qb->expr()->eq('c.validated', '?2')
        );
        
        $eventByUser = $qb->expr()->andX(
            $qb->expr()->eq('c.lang', '?1'),
            $qb->expr()->eq('c.validated', '?3'),
            $qb->expr()->eq('c.userId', '?4')
        );
        
        $qb->andWhere($qb->expr()->orX($eventByLang, $eventByUser))
            ->setParameters([
                1 => $userLangId,
                2 => 1,
                3 => 0,
                4 => $user->getId(),
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

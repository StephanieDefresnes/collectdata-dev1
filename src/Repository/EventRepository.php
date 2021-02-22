<?php

namespace App\Repository;

use App\Entity\Event;
use App\Service\LangService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    private $langService;
    
    public function __construct(ManagerRegistry $registry, LangService $langService)
    {
        parent::__construct($registry, Event::class);
        
        $this->langService = $langService;
    }
    
    public function findLocaleEvents()
    {
        $locale_lang_id = $this->langService->getLangIdByLang(locale_get_default());
        
        return $this->createQueryBuilder('c')
                    ->andWhere("c.langId = ?1")
                    ->andWhere("c.validated = ?2")
                    ->setParameter(1, $locale_lang_id)
                    ->setParameter(2, 1)
                    ->select('c.id, c.title')
                    ->getQuery()
                    ->getResult();
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

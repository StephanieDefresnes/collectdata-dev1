<?php

namespace App\Repository;

use App\Entity\Lang;
use App\Entity\Situ;
use App\Entity\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Situ|null find($id, $lockMode = null, $lockVersion = null)
 * @method Situ|null findOneBy(array $criteria, array $orderBy = null)
 * @method Situ[]    findAll()
 * @method Situ[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SituRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Situ::class);
    }
    
    /**
     * @return []   Returns an array with count user situs by lang
     * 
     * @param type $userId
     * @return type
     */
    public function findUserSitusCountByLang($userId)
    {
        $situs = $this->_em->createQueryBuilder()
            ->from(Situ::class,'situ')
            ->select('  count(situ.id)          situs, 
                        situ.translatedSituId   translatedSituId,
                        lang.name               langName')
            ->leftJoin(Lang::class, 'lang', 'WITH', 'situ.lang=lang.id')
            ->groupBy('lang.id')
            ->andWhere('situ.user = ?1')
            ->andWhere('situ.status = ?2')
            ->setParameter(1, $userId)
            ->setParameter(2, 3)
            ->getQuery()
            ->getScalarResult();
        
        $result = [];
        foreach ($situs as $situ) {
            $result[] = [ 
                'situs' => $situ['situs'],
                'translatedSituId' => $situ['translatedSituId'],
                'langName' => html_entity_decode($situ['langName'], ENT_QUOTES, 'UTF-8'),
            ];
        }
        
        return $result;
    }
    
    /**
     * @return []   Returns an array transation situs
     *              if validation requested or validated
     *              **join because of Invalid PathExpression on situ.status
     * 
     * @param type $situId
     * @param type $langId
     * @return type
     */
    public function findTranslations($situId, $langId)
    {
        $qb = $this->_em->createQueryBuilder();        
        $qb->from(Situ::class,'situ')
            ->select('situ.id, situ.title, status.id as statusId')
            ->leftJoin(Status::class, 'status', 'WITH', 'situ.status=status.id')
            ->where('situ.translatedSituId = ?1')
            ->andWhere('situ.lang = ?2')
            ->andWhere($qb->expr()->neq('situ.status', '?3'))
            ->andWhere($qb->expr()->neq('situ.status', '?4'))
            ->andWhere($qb->expr()->neq('situ.status', '?5'))
            ->setParameters([
                1 => $situId,
                2 => $langId,
                3 => $this->_em->getRepository(Status::class)->find(1),
                4 => $this->_em->getRepository(Status::class)->find(4),
                5 => $this->_em->getRepository(Status::class)->find(5)
            ]);
        
        return $qb->getQuery()->getResult();
    }

    // /**
    //  * @return Situ[] Returns an array of Situ objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Situ
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

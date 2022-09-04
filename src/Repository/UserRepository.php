<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }
    
    /**
     * @return user array expect # <= 0 (anonymous & FLP admin)
     */
    public function findUsers()
    {
        $query = $this->_em->createQueryBuilder()
            ->from(User::class,'u')
            ->select('u')
            ->andWhere('u.id > ?1')
            ->setParameter(1, 0);
        
        return $query->getQuery()->getResult();
    }
    
    /*
     * @return user array for specific role
     */
    public function findRole($role)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $qb->from(User::class,'u')
            ->select('u')
            ->andWhere($qb->expr()->like('u.roles', '?1'))
            ->setParameter(1, '%'.$role.'%');
        
        return $qb->getQuery()->getResult();
    }
    
    /*
     * @return user array by role for specific lang
     */
    public function findRoleByLang($role, $lang)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $qb->from(User::class,'u')
            ->select('u')
            ->andWhere($qb->expr()->like('u.roles', '?1'))
            ->andWhere('?2 MEMBER OF u.langs')
            ->setParameter(1, '%'.$role.'%')
            ->setParameter(2, $lang);
        
        return $qb->getQuery()->getResult();
    }
    
    /*
     * @return langContributor users by lang
     */    
    public function findLangContributors($lang)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $qb->from(User::class,'u')
            ->select('u')
            ->andWhere('?1 MEMBER OF u.langs OR ?1 MEMBER OF u.contributorLangs')
            ->andWhere('u.langContributor = ?2')
            ->setParameter(1, $lang)
            ->setParameter(2, true);
        
        return $qb->getQuery()->getResult();
    }
    
    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

}
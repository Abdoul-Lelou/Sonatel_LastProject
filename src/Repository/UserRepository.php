<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByRoles()
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('u')->from('AppBundle:User', 'u')->where('u.role LIKE :role')->setParameter('role', '%"'.'ROLE_MEDECIN'.'"%');

        return   $qb->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function findOneBySome()
    {
        // $qb = $this->_em->createQueryBuilder();
        // $qb->select('u')
        // ->from(User, 'u')
        // ->where('u.role LIKE :role')
        // ->setParameter('role', '%"'.$role.'"%');

        // return $qb->getQuery()->getResult();

        $query = $this->getDoctrine()->getEntityManager()
            ->createQuery(
                'SELECT u FROM MyBundle:User u WHERE u.role LIKE :role'
            )->setParameter('role', '%"ROLE_MEDECIN"%');

        return $query->getResult();
    }

    /*
     * @return User[] Returns an array of User objects
     */
    //  /*
    public function findOneByrole()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.role = :val')
            ->setParameter('val', 'ROLE_MEDECIN')
         //   ->orderBy('u.id', 'ASC')
          //  ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    //   */

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

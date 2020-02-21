<?php

namespace App\Repository;

use App\Entity\AffecterCompte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method AffecterCompte|null find($id, $lockMode = null, $lockVersion = null)
 * @method AffecterCompte|null findOneBy(array $criteria, array $orderBy = null)
 * @method AffecterCompte[]    findAll()
 * @method AffecterCompte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AffecterCompteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AffecterCompte::class);
    }

    // /**
    //  * @return AffecterCompte[] Returns an array of AffecterCompte objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AffecterCompte
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

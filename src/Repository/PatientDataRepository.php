<?php

namespace App\Repository;

use App\Entity\PatientData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PatientData|null find($id, $lockMode = null, $lockVersion = null)
 * @method PatientData|null findOneBy(array $criteria, array $orderBy = null)
 * @method PatientData[]    findAll()
 * @method PatientData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PatientDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PatientData::class);
    }

    // /**
    //  * @return PatientData[] Returns an array of PatientData objects
    //  */

    public function findPatientDataByPatientId($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.patient = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
           // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPatientByPatientDataId($value)
    {
        return $this->createQueryBuilder('p')
            ->Where('p.patient = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?PatientData
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

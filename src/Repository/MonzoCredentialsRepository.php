<?php

namespace App\Repository;

use App\Entity\MonzoCredentials;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method MonzoCredentials|null find($id, $lockMode = null, $lockVersion = null)
 * @method MonzoCredentials|null findOneBy(array $criteria, array $orderBy = null)
 * @method MonzoCredentials[]    findAll()
 * @method MonzoCredentials[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MonzoCredentialsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MonzoCredentials::class);
    }

    // /**
    //  * @return MonzoCredentials[] Returns an array of MonzoCredentials objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MonzoCredentials
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

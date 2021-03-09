<?php

namespace App\Repository;

use App\Entity\PicturesOld;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PicturesOld|null find($id, $lockMode = null, $lockVersion = null)
 * @method PicturesOld|null findOneBy(array $criteria, array $orderBy = null)
 * @method PicturesOld[]    findAll()
 * @method PicturesOld[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PicturesOldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PicturesOld::class);
    }

    // /**
    //  * @return PictureOld[] Returns an array of PictureOld objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PictureOld
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

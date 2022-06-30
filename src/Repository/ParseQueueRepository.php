<?php

namespace App\Repository;

use App\Entity\ParseQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParseQueue>
 *
 * @method ParseQueue|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParseQueue|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParseQueue[]    findAll()
 * @method ParseQueue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParseQueueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParseQueue::class);
    }

    public function add(ParseQueue $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ParseQueue $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return ParseQueue[] Returns an array of ParseQueue objects
     */
    public function findAllNew(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :val')
            ->setParameter('val', ParseQueue::STATUS_NEW)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ParseQueue[]
     */
    public function findAllSuccess(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :val')
            ->setParameter('val', ParseQueue::STATUS_SUCCESS)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    public function findOneBySomeField($value): ?ParseQueue
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

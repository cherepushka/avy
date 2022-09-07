<?php

namespace App\Repository;

use App\Entity\FileStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileStatus>
 *
 * @method FileStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method FileStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method FileStatus[]    findAll()
 * @method FileStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileStatusRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileStatus::class);
    }

    public function add(FileStatus $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FileStatus $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
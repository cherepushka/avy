<?php

namespace App\Repository;

use App\Entity\FileType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileType>
 *
 * @method FileType|null find($id, $lockMode = null, $lockVersion = null)
 * @method FileType|null findOneBy(array $criteria, array $orderBy = null)
 * @method FileType[]    findAll()
 * @method FileType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileTypeRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileType::class);
    }

    public function add(FileType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FileType $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
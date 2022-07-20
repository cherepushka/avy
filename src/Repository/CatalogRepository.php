<?php

namespace App\Repository;

use App\Entity\Catalog;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Catalog>
 *
 * @method Catalog|null find($id, $lockMode = null, $lockVersion = null)
 * @method Catalog|null findOneBy(array $criteria, array $orderBy = null)
 * @method Catalog[]    findAll()
 * @method Catalog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CatalogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Catalog::class);
    }

    public function add(Catalog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Catalog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByFilename(string $filename): ?Catalog
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.filename = :val')
            ->setParameter('val', $filename)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllByOriginFilename(string $filename): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.origin_filename = :val')
            ->setParameter('val', $filename)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $catalog_id
     * @return Category[]
     */
    public function findAllSeries(int $catalog_id): array
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(Category::class, 'cat');

        $query = $this->_em->createNativeQuery('
            SELECT cat.* FROM catalog_category cc
                LEFT JOIN category cat ON cc.category_id = cat.id
                LEFT JOIN category cat2 ON cat.id = cat2.parent 
            WHERE cc.catalog_id = ?
            AND cat2.id IS NULL
        ', $rsm);

        $query->setParameter(1, $catalog_id);

        return $query->getResult();
    }

    /**
     * @param int $byte_size
     * @return Catalog[]
     */
    public function findAllByByteSize(int $byte_size): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.byte_size = :val')
            ->setParameter('val', $byte_size)
            ->getQuery()
            ->getResult();
    }
}

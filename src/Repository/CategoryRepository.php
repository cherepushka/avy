<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function add(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /** @return Category[] */
    public function findAllWithoutChildren(): array
    {
        return $this->createQueryBuilder('c1')
            ->leftJoin(Category::class, 'c2', Expr\Join::WITH, 'c2.parent = c1.id')
            ->where('c2.id IS NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * $param [int]array $ids
     */
    public function findWithoutChildren(array $ids): array
    {
        return $this->createQueryBuilder('c1')
            ->leftJoin(Category::class, 'c2', Expr\Join::WITH, 'c2.parent = c1.id')
            ->where('c2.id IS NULL')
            ->andWhere('c1.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    public function findAllParentsList(int $id): array
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(Category::class, 'cc');

        $query = $this->_em->createNativeQuery('
            WITH RECURSIVE cte_categories AS (
                SELECT `id`, `parent`, `title`, `link` FROM category WHERE id = ?

                UNION ALL

                SELECT cats.id, cats.parent, cats.title, cats.link
                FROM category cats, cte_categories cte_cats
                WHERE cats.id = cte_cats.parent
            )

            SELECT id, parent, title, link FROM cte_categories cc
        ', $rsm);
        $query->setParameter(1, $id);

        return $query->getResult();
    }

}

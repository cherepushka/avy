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

    /**
     * @param array<int> $category_ids
     * @return array<Category>
     */
    public function findOnlyFinalCats(array $category_ids): array
    {
        $IN_STATEMENT = '';
        foreach ($category_ids as $category_id) {
            $IN_STATEMENT .= "$category_id,";
        }
        $IN_STATEMENT = rtrim($IN_STATEMENT, ',');

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(Category::class, 'c1.*');

        $query = $this->_em->createNativeQuery("
            SELECT c1.* FROM category c1
            LEFT JOIN category c2 ON c1.id = c2.parent
            WHERE 
                c1.id IN ($IN_STATEMENT)
                AND
                c2.id IS NULL
        ", $rsm);

        return $query->getResult();
    }

//    /**
//     * @return CategoryTree[] Returns an array of CategoryTree objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CategoryTree
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

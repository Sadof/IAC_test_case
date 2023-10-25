<?php

namespace App\Repository;

use App\Entity\ProductCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductCategory>
 *
 * @method ProductCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductCategory[]    findAll()
 * @method ProductCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductCategory::class);
    }

    public function getLowestLevel()
    {
        $exclude = $this->createQueryBuilder('p')
            ->select("p.parent_id")
            ->groupBy('p.parent_id')
            ->getQuery()
            ->getResult();

        $exclude = array_column($exclude, 'parent_id');
        $exclude = array_filter($exclude, fn ($value) => !is_null($value) && $value !== '');

        return $this->createQueryBuilder('p')
            ->andWhere('p.id NOT IN (:exclude) AND p.parent_id IS NOT NULL')
            ->setParameter('exclude', $exclude)
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return ProductCategory[] Returns an array of ProductCategory objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ProductCategory
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

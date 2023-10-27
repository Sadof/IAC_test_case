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

    /**
     * Получение элементов древовидного справочника, неимеющий детей
     *
     * @return void
     */
    public function getLowestLevel()
    {
        $exclude = $this->createQueryBuilder('e')
            ->select("e.parent_id")
            ->where("e.parent_id IS NOT NULL")
            ->distinct();
            
        $data = $this->createQueryBuilder('p');
        $data->where($data->expr()->notIn('p.id', $exclude->getDQL()));

        return $data->getQuery()->getResult();
    }
}

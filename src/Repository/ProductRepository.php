<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{

    public const PAGINATOR_PER_PAGE = 20;
    public EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Product::class);
        $this->entityManager = $entityManager;
    }

    /**
     * Получение отфильтрованного, сортированного списка продуктов на основе данных из запроса
     *
     * @param Request $request
     * @return void
     */
    public function getData(Request $request)
    {

        $query = $this->createQueryBuilder('c');
        if (
            $request->query->has("search_field") && $request->query->has("search_value") &&
            $request->query->get("search_field") && $request->query->get("search_value")
        ) {
            $search_field = $request->query->get("search_field");
            $search_value = $request->query->get("search_value");
            $fieldMappings = $this->entityManager->getClassMetadata(Product::class)->fieldMappings;
            $associationMappings = $this->entityManager->getClassMetadata(Product::class)->associationMappings;
            if (array_key_exists($search_field, $fieldMappings)) {
                $field_type = $fieldMappings[$search_field]["type"];
                if ($field_type == "string" || $field_type == "text") {
                    $query->andWhere("c.{$search_field} LIKE :value")
                        ->setParameter('value', '%' . $search_value . '%');
                } else if (($field_type == "integer" && filter_var($search_value, FILTER_VALIDATE_INT) !== false)
                    || ($field_type == "float" && filter_var($search_value, FILTER_VALIDATE_FLOAT) !== false)
                ) {
                    $query->andWhere("c.{$search_field} = :value")
                        ->setParameter('value', $search_value);
                } else if ($field_type == "date" || $field_type == "datetime") {

                    $dates = explode(",", $search_value);

                    if (count($dates) <= 2) {
                        if (count($dates) == 2) {
                            $from = date($dates[0]);
                            $to = date($dates[1]);
                        } else if (count($dates) == 1) {
                            $from = date($dates[0]);
                            $to = date($dates[0]);
                        }
                        $query->where("c.{$search_field} BETWEEN :from AND :to")
                            ->setParameter('from', $from . ' 00:00:00')
                            ->setParameter('from', $from . ' 00:00:00')
                            ->setParameter('to', $to . ' 23:59:59');
                    }
                }
            }

            if (array_key_exists($search_field, $associationMappings)) {
                $query->andWhere("c.{$search_field} = :value")
                    ->setParameter('value', $search_value);
            }
        }
        if ($request->query->has("sort_field") && $request->query->has("order")) {
            $query->orderBy("c." . $request->query->get("sort_field"), $request->query->get("order") == 1 ? "DESC" : "ASC");
        } else {
            $query->orderBy("c.id", "DESC");
        }

        $page = $request->query->get("page", 1) - 1;
        if ($page >= 0) {
            $offset = $page * self::PAGINATOR_PER_PAGE;
            $query->setMaxResults(self::PAGINATOR_PER_PAGE)
                ->setFirstResult($offset);
        }
        $query->getQuery();
        return new Paginator($query);
    }
}

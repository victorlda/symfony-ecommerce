<?php

declare(strict_types=1);

namespace App\Catalog\Repository;

use App\Catalog\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $product): void
    {
        $this->getEntityManager()->persist($product);
        $this->getEntityManager()->flush();
    }

    public function findOneBySku(string $sku): ?Product
    {
        return $this->findOneBy(['sku' => mb_strtoupper(trim($sku))]);
    }

    /**
     * @return array{items: list<Product>, total: int}
     */
    public function findActivePaginated(int $page, int $limit): array
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.active = true');

        return $this->paginate($query, $page, $limit);
    }

    /**
     * @return array{items: list<Product>, total: int}
     */
    public function findForAdmin(?bool $active, ?string $search, int $page, int $limit): array
    {
        $query = $this->createQueryBuilder('p');

        if (null !== $active) {
            $query->andWhere('p.active = :active')->setParameter('active', $active);
        }

        if (null !== $search && '' !== trim($search)) {
            $term = '%'.addcslashes(mb_strtolower(trim($search)), '%_\\').'%';
            $query->andWhere('LOWER(p.name) LIKE :term OR LOWER(p.sku) LIKE :term')
                ->setParameter('term', $term);
        }

        return $this->paginate($query, $page, $limit);
    }

    /**
     * @return array{items: list<Product>, total: int}
     */
    private function paginate(QueryBuilder $query, int $page, int $limit): array
    {
        $query
            ->orderBy('p.createdAt', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($query);

        return [
            'items' => array_values(iterator_to_array($paginator)),
            'total' => \count($paginator),
        ];
    }
}

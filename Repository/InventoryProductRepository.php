<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Bundle\ProductBundle\Entity\InventoryProduct;
use Ekyna\Bundle\ProductBundle\Model\InventoryInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Class InventoryProductRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @implements EntityRepository<InventoryProduct>
 */
class InventoryProductRepository extends ServiceEntityRepository
{
    private ?Query $findOneNotAppliedByInventoryQuery = null;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryProduct::class);
    }

    public function findByInventory(InventoryInterface $inventory): array
    {
        return $this->findBy([
            'inventory' => $inventory,
        ]);
    }

    public function findOneNotAppliedByInventory(InventoryInterface $inventory): ?InventoryProduct
    {
        return $this
            ->getFindOneNotAppliedByInventory()
            ->setParameter('inventory', $inventory)
            ->getOneOrNullResult();
    }

    private function getFindOneNotAppliedByInventory(): Query
    {
        if (null !== $this->findOneNotAppliedByInventoryQuery) {
            return $this->findOneNotAppliedByInventoryQuery;
        }

        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        return $this->findOneNotAppliedByInventoryQuery = $qb
            ->andWhere($ex->eq('p.inventory', ':inventory'))
            ->andWhere($ex->isNotNull('p.realStock'))
            ->andWhere($ex->neq('p.initialStock', 'p.realStock'))
            ->andWhere($ex->orX(
                $ex->isNull('p.appliedStock'),
                $ex->neq(0, 'p.realStock - p.initialStock - p.appliedStock')
            ))
            ->setMaxResults(1)
            ->getQuery();
    }

    public function findOneByInventoryAndProduct(
        InventoryInterface $inventory,
        ProductInterface   $product
    ): ?InventoryProduct {
        return $this->findOneBy([
            'inventory' => $inventory,
            'product'   => $product,
        ]);
    }
}

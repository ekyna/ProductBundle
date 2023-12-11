<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Bundle\ProductBundle\Entity\InventoryProduct;
use Ekyna\Bundle\ProductBundle\Model\InventoryInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;

/**
 * Class InventoryProductRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @implements EntityRepository<InventoryProduct>
 */
class InventoryProductRepository extends ServiceEntityRepository
{
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

    public function findByInventoryWithRealStock(InventoryInterface $inventory): array
    {
        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        return $qb
            ->andWhere($ex->eq('p.inventory', ':inventory'))
            ->andWhere($ex->isNotNull('p.realStock'))
            ->getQuery()
            ->setParameter('inventory', $inventory)
            ->getResult();
    }

    public function findOneNotAppliedByInventory(InventoryInterface $inventory, bool $bundle): ?InventoryProduct
    {
        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        if ($bundle) {
            $types = [ProductTypes::TYPE_BUNDLE];
            $clause = $ex->andX(
                $ex->gt('p.realStock', 0),
                $ex->orX(
                    $ex->isNull('p.appliedStock'),
                    $ex->neq(0, 'p.realStock - p.appliedStock')
                )
            );
        } else {
            $types = [ProductTypes::TYPE_SIMPLE, ProductTypes::TYPE_VARIANT];
            $clause = $ex->andX(
                $ex->neq('p.initialStock', 'p.realStock'),
                $ex->orX(
                    $ex->isNull('p.appliedStock'),
                    $ex->neq(0, 'p.realStock - p.initialStock - p.appliedStock')
                )
            );
        }

        return $qb
            ->join('p.product', 'p2')
            ->andWhere($ex->eq('p.inventory', ':inventory'))
            ->andWhere($ex->in('p2.type', ':types'))
            ->andWhere($ex->isNotNull('p.realStock'))
            ->andWhere($clause)
            ->setParameter('types', $types)
            ->setMaxResults(1)
            ->getQuery()
            ->setParameter('inventory', $inventory)
            ->getOneOrNullResult();
    }

    public function findBundlesByInventory(InventoryInterface $inventory): array
    {
        $qb = $this->createQueryBuilder('p');
        $ex = $qb->expr();

        return $qb
            ->join('p.product', 'p2')
            ->andWhere($ex->eq('p.inventory', ':inventory'))
            ->andWhere($ex->eq('p2.type', ':type'))
            ->andWhere($ex->isNotNull('p.realStock'))
            ->andWhere($ex->gt('p.realStock', 0))
            ->setParameter('type', ProductTypes::TYPE_BUNDLE)
            ->getQuery()
            ->setParameter('inventory', $inventory)
            ->getResult();
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

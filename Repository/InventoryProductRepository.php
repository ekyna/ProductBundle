<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
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

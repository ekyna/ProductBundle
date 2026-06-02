<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Model\PriceGridRuleInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class PriceGridRuleRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceGridRuleRepository extends ResourceRepository implements PriceGridRuleRepositoryInterface
{
    public function findByProductAndGroupAndQuantity(
        ProductInterface $product,
        CustomerGroupInterface   $group,
        Decimal          $quantity
    ): ?PriceGridRuleInterface {
        $qb = $this->createQueryBuilder('r');

        return $qb
            ->join('r.grid', 'g')
            ->andWhere($qb->expr()->eq('g.product', ':product'))
            ->andWhere($qb->expr()->eq('g.group', ':group'))
            ->andWhere($qb->expr()->lte('r.quantity', ':quantity'))
            ->setParameter('product', $product)
            ->setParameter('group', $group)
            ->setParameter('quantity', $quantity->ceil()->toInt())
            ->addOrderBy('r.quantity', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function findByProduct(ProductInterface $product): array
    {
        $qb = $this->createQueryBuilder('r');

        return $qb
            ->join('r.grid', 'g')
            ->andWhere($qb->expr()->eq('g.product', ':product'))
            ->setParameter('product', $product)
            ->getQuery()
            ->getResult();
    }
}

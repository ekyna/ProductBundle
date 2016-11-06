<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class ProductStockUnitRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductStockUnitRepository extends ResourceRepository implements StockUnitRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findAvailableOrPendingBySubject(StockSubjectInterface $subject)
    {
        if (!$subject->getId()) {
            return [];
        }

        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->in('o.product', ':product'))
            ->andWhere($qb->expr()->in('o.state', ':state'))
            ->setParameter('product', $subject)
            ->setParameter('state', [StockUnitStates::STATE_OPENED, StockUnitStates::STATE_PENDING])
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findNewBySubject(StockSubjectInterface $subject)
    {
        if (!$subject->getId()) {
            return [];
        }

        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->in('o.product', ':product'))
            ->andWhere($qb->expr()->in('o.state', ':states'))
            ->setParameter('product', $subject)
            ->setParameter('state', StockUnitStates::STATE_NEW)
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findOneBySupplierOrderItem(SupplierOrderItemInterface $item)
    {
        if (!$item->getId()) {
            return null;
        }

        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq('o.supplierOrderItem', ':item'))
            ->setParameter('item', $item)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class ProductStockUnitRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductStockUnitRepository extends ResourceRepository implements StockUnitRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function findNewBySubject(StockSubjectInterface $subject)
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_NEW,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function findPendingBySubject(StockSubjectInterface $subject)
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_PENDING,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function findReadyBySubject(StockSubjectInterface $subject)
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_READY,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function findPendingOrReadyBySubject(StockSubjectInterface $subject)
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_PENDING,
            StockUnitStates::STATE_READY,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function findNotClosedBySubject(StockSubjectInterface $subject)
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_NEW,
            StockUnitStates::STATE_PENDING,
            StockUnitStates::STATE_READY,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function findAssignableBySubject(StockSubjectInterface $subject)
    {
        if (!$subject instanceof ProductInterface) {
            throw new InvalidArgumentException('Expected instance of ' . ProductInterface::class);
        }

        if (!$subject->getId()) {
            return [];
        }

        $alias = $this->getAlias();
        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull($alias . '.supplierOrderItem'), // Not yet linked to a supplier order
                $qb->expr()->lt(                                    // Sold lower than ordered + adjusted
                    $alias . '.soldQuantity',
                    $qb->expr()->sum($alias . '.orderedQuantity', $alias . '.adjustedQuantity')
                )
            ))
            ->setParameter('product', $subject)
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findLinkableBySubject(StockSubjectInterface $subject)
    {
        if (!$subject instanceof ProductInterface) {
            throw new InvalidArgumentException('Expected instance of ' . ProductInterface::class);
        }

        if (!$subject->getId()) {
            return null;
        }

        $alias = $this->getAlias();
        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
            ->andWhere($qb->expr()->isNull($alias . '.supplierOrderItem'))// Not yet linked to a supplier order
            ->setParameter('product', $subject)
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds stock units by subject and states.
     *
     * @param StockSubjectInterface $subject
     * @param array                 $states
     * @param string                $sort
     *
     * @return array
     */
    private function findBySubjectAndStates(StockSubjectInterface $subject, array $states, $sort = 'ASC')
    {
        if (!$subject instanceof ProductInterface) {
            throw new InvalidArgumentException('Expected instance of ' . ProductInterface::class);
        }

        if (empty($states)) {
            throw new InvalidArgumentException('Expected at least one state.');
        }

        if (!$subject->getId()) {
            return [];
        }

        $alias = $this->getAlias();
        $qb = $this->getQueryBuilder();

        if (1 == count($states)) {
            $qb
                ->andWhere($qb->expr()->eq($alias . '.state', ':state'))
                ->setParameter('state', reset($states));
        } else {
            $qb
                ->andWhere($qb->expr()->in($alias . '.state', ':states'))
                ->setParameter('states', $states);
        }

        return $qb
            ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
            ->addOrderBy($alias . '.createdAt', $sort)
            ->setParameter('product', $subject)
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findInStock()
    {
        $qb = $this->getQueryBuilder('psu');

        $inStock = $qb->expr()->diff(
            $qb->expr()->sum('psu.receivedQuantity', 'psu.adjustedQuantity'),
            'psu.shippedQuantity'
        );

        return $qb
            ->join('psu.product', 'p')
            ->andWhere($qb->expr()->gt($inStock, 0))
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findLatestClosedBySubject(StockSubjectInterface $subject, $limit = 3)
    {
        if (!$subject instanceof ProductInterface) {
            throw new InvalidArgumentException('Expected instance of ' . ProductInterface::class);
        }

        if (!$subject->getId()) {
            return [];
        }

        $alias = $this->getAlias();
        $qb = $this->getQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->eq($alias . '.product', ':product'))
            ->andWhere($qb->expr()->eq($alias . '.state', ':state'))
            ->addOrderBy($alias . '.closedAt', 'DESC')
            ->setParameters([
                'product' => $subject,
                'state'   => StockUnitStates::STATE_CLOSED,
            ])
            ->getQuery()
            ->setMaxResults($limit)
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'psu';
    }
}

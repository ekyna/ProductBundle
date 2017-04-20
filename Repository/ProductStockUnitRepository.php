<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Repository\StockUnitRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class ProductStockUnitRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductStockUnitRepository extends ResourceRepository implements StockUnitRepositoryInterface
{
    public function findNewBySubject(StockSubjectInterface $subject): array
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_NEW,
        ]);
    }

    public function findPendingBySubject(StockSubjectInterface $subject): array
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_PENDING,
        ]);
    }

    public function findReadyBySubject(StockSubjectInterface $subject): array
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_READY,
        ]);
    }

    public function findPendingOrReadyBySubject(StockSubjectInterface $subject): array
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_PENDING,
            StockUnitStates::STATE_READY,
        ]);
    }

    public function findNotClosedBySubject(StockSubjectInterface $subject): array
    {
        return $this->findBySubjectAndStates($subject, [
            StockUnitStates::STATE_NEW,
            StockUnitStates::STATE_PENDING,
            StockUnitStates::STATE_READY,
        ]);
    }

    public function findAssignableBySubject(StockSubjectInterface $subject): array
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
                $qb->expr()->andX(
                    $qb->expr()->isNull($alias . '.supplierOrderItem'), // Not linked to a supplier order
                    $qb->expr()->eq($alias . '.adjustedQuantity', 0)    // Not adjusted
                ),
                $qb->expr()->lt(                                    // Sold lower than ordered + adjusted
                    $alias . '.soldQuantity',
                    $qb->expr()->sum($alias . '.orderedQuantity', $alias . '.adjustedQuantity')
                )
            ))
            ->setParameter('product', $subject)
            ->getQuery()
            ->getResult();
    }

    public function findLinkableBySubject(StockSubjectInterface $subject): array
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
            ->andWhere($qb->expr()->neq($alias . '.state', ':state'))      // Not closed
            ->andWhere($qb->expr()->isNull($alias . '.supplierOrderItem')) // Not linked to a supplier order
            ->andWhere($qb->expr()->eq($alias . '.adjustedQuantity', 0))   // Not adjusted
            ->setParameters([
                'product' => $subject,
                'state'   => StockUnitStates::STATE_CLOSED,
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds stock units by subject and states.
     */
    private function findBySubjectAndStates(StockSubjectInterface $subject, array $states, string $sort = 'ASC'): array
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

    public function findInStock(): array
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

    public function findLatestNotClosedBySubject(StockSubjectInterface $subject, int $limit = 3): array
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
            ->andWhere($qb->expr()->neq($alias . '.state', ':state'))
            ->addOrderBy($alias . '.createdAt', 'DESC')
            ->setParameters([
                'product' => $subject,
                'state'   => StockUnitStates::STATE_CLOSED,
            ])
            ->getQuery()
            ->setMaxResults($limit)
            ->getResult();
    }

    public function findLatestClosedBySubject(StockSubjectInterface $subject, int $limit = 3): array
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

    protected function getAlias(): string
    {
        return 'psu';
    }
}

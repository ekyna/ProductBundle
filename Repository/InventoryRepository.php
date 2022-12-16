<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\InventoryInterface;
use Ekyna\Bundle\ProductBundle\Model\InventoryState;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class InventoryRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InventoryRepository extends ResourceRepository implements InventoryRepositoryInterface
{
    public function findOneOpened(): ?InventoryInterface
    {
        $qb = $this->createQueryBuilder('i');

        return $qb
            ->andWhere($qb->expr()->eq('i.state', ':state'))
            ->getQuery()
            ->setParameter('state', InventoryState::OPENED->value)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function findOneNotClosed(): ?InventoryInterface
    {
        $qb = $this->createQueryBuilder('i');

        return $qb
            ->andWhere($qb->expr()->neq('i.state', ':state'))
            ->getQuery()
            ->setParameter('state', InventoryState::CLOSED->value)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}

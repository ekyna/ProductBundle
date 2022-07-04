<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\ProductBundle\Model\CatalogInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class CatalogRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CatalogRepository extends ResourceRepository implements CatalogRepositoryInterface
{
    public function findByCustomer(CustomerInterface $customer): array
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->andWhere($qb->expr()->eq('c.customer', ':customer'))
            ->addOrderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->setParameters([
                'customer' => $customer,
            ])
            ->getResult();
    }

    public function findOneByCustomerAndId(CustomerInterface $customer, int $id): ?CatalogInterface
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->andWhere($qb->expr()->eq('c.customer', ':customer'))
            ->andWhere($qb->expr()->eq('c.id', ':id'))
            ->getQuery()
            ->setParameters([
                'customer' => $customer,
                'id'       => $id,
            ])
            ->getOneOrNullResult();
    }

    protected function getAlias(): string
    {
        return 'c';
    }
}

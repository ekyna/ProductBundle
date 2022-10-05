<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\ProductBundle\Model\CatalogInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class CatalogRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CatalogRepository extends ResourceRepository implements CatalogRepositoryInterface
{
    public function findByCustomer(CustomerInterface $customer): iterable
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

    public function findByProduct(ProductInterface $product, int $limit = null): iterable
    {
        $qb = $this->createQueryBuilder('c');

        $query = $qb
            ->join('c.pages', 'p')
            ->join('p.slots', 's')
            ->andWhere($qb->expr()->eq('s.product', ':product'))
            ->addOrderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->setParameters([
                'product' => $product,
            ])
            ->setMaxResults($limit);

        if (1 < $limit) {
            return new Paginator($query, true);
        }

        return $query->getResult();
    }

    protected function getAlias(): string
    {
        return 'c';
    }
}

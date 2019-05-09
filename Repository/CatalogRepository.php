<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class CatalogRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CatalogRepository extends ResourceRepository
{
    /**
     * Finds catalogs by customer.
     *
     * @param CustomerInterface $customer
     *
     * @return \Ekyna\Bundle\ProductBundle\Entity\Catalog[]
     */
    public function findByCustomer(CustomerInterface $customer)
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

    /**
     * Finds a catalog by customer and id.
     *
     * @param CustomerInterface $customer
     * @param int               $id
     *
     * @return \Ekyna\Bundle\ProductBundle\Entity\Catalog|null
     */
    public function findOneByCustomerAndId(CustomerInterface $customer, int $id)
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

    /**
     * @inheritDoc
     */
    protected function getAlias()
    {
        return 'c';
    }
}

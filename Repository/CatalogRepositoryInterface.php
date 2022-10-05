<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\ProductBundle\Model\CatalogInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Class CatalogRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<CatalogInterface>
 */
interface CatalogRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * @return iterable<CatalogInterface>
     */
    public function findByCustomer(CustomerInterface $customer): iterable;

    public function findOneByCustomerAndId(CustomerInterface $customer, int $id): ?CatalogInterface;

    /**
     * @return iterable<CatalogInterface>
     */
    public function findByProduct(ProductInterface $product): iterable;
}

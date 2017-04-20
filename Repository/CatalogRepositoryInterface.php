<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\CommerceBundle\Model\CustomerInterface;
use Ekyna\Bundle\ProductBundle\Model\CatalogInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Class CatalogRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CatalogRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * @return array<CatalogInterface>
     */
    public function findByCustomer(CustomerInterface $customer): array;

    public function findOneByCustomerAndId(CustomerInterface $customer, int $id): ?CatalogInterface;
}

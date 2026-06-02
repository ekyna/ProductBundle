<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Model\PriceGridRuleInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Class PriceGridRuleRepository
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PriceGridRuleRepositoryInterface extends ResourceRepositoryInterface
{
    public function findByProductAndGroupAndQuantity(
        ProductInterface       $product,
        CustomerGroupInterface $group,
        Decimal                $quantity
    ): ?PriceGridRuleInterface;

    public function findByProduct(ProductInterface $product): array;
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\PricingInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface PricingRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<PricingInterface>
 */
interface PricingRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the pricing rules by product.
     */
    public function findRulesByProduct(ProductInterface $product): array;

    /**
     * Finds applicable pricing for the given context.
     */
    public function findByContext(ContextInterface $context): array;
}

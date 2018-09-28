<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Context\ContextInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface PricingRepositoryInterface
 * @package Ekyna\Bundle\ProductBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PricingRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the pricing rules by product.
     *
     * @param Model\ProductInterface $product
     *
     * @return array
     */
    public function findRulesByProduct(Model\ProductInterface $product);

    /**
     * Finds applicable pricings for the given context.
     *
     * @param ContextInterface $context
     *
     * @return Model\PricingInterface[]
     */
    public function findByContext(ContextInterface $context);
}

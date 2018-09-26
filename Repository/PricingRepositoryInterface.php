<?php

namespace Ekyna\Bundle\ProductBundle\Repository;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
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
     * @param ProductInterface $product
     *
     * @return array
     */
    public function findRulesByProduct(ProductInterface $product);
}

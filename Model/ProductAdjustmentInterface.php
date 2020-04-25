<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;

/**
 * Interface ProductAdjustmentInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductAdjustmentInterface extends AdjustmentInterface
{
    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct(): ?ProductInterface;

    /**
     * Sets the product.
     *
     * @param ProductInterface $product
     *
     * @return $this|ProductAdjustmentInterface
     */
    public function setProduct(ProductInterface $product = null): ProductAdjustmentInterface;
}

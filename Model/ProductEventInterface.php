<?php

namespace Ekyna\Bundle\ProductBundle\Model;

/**
 * Interface ProductEventInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductEventInterface
{
    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct();
}

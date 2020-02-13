<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Interface ProductStockUnitInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductStockUnitInterface extends StockUnitInterface
{
    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface;

    /**
     * Sets the product.
     *
     * @param ProductInterface $product
     *
     * @return $this|ProductStockUnitInterface
     */
    public function setProduct(ProductInterface $product): ProductStockUnitInterface;
}

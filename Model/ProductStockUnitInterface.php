<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Interface ProductStockUnitInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductStockUnitInterface extends StockUnitInterface
{
    public function getProduct(): ?ProductInterface;

    public function setProduct(ProductInterface $product): ProductStockUnitInterface;
}

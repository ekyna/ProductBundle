<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class ProductStockUnitEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ProductStockUnitEvents
{
    const INSERT = 'ekyna_product.product_stock_unit.insert';
    const UPDATE = 'ekyna_product.product_stock_unit.update';
    const DELETE = 'ekyna_product.product_stock_unit.delete';
}

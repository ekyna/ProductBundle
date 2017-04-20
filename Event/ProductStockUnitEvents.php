<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class ProductStockUnitEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ProductStockUnitEvents
{
    public const INSERT = 'ekyna_product.product_stock_unit.insert';
    public const UPDATE = 'ekyna_product.product_stock_unit.update';
    public const DELETE = 'ekyna_product.product_stock_unit.delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

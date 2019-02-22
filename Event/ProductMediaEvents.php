<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class ProductMediaEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ProductMediaEvents
{
    const INSERT = 'ekyna_product.product_media.insert';
    const UPDATE = 'ekyna_product.product_media.update';
    const DELETE = 'ekyna_product.product_media.delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

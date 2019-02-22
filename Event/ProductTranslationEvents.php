<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class ProductTranslationEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ProductTranslationEvents
{
    const INSERT = 'ekyna_product.product_translation.insert';
    const UPDATE = 'ekyna_product.product_translation.update';
    const DELETE = 'ekyna_product.product_translation.delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

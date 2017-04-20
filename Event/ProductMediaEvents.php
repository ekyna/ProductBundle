<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class ProductMediaEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ProductMediaEvents
{
    public const INSERT = 'ekyna_product.product_media.insert';
    public const UPDATE = 'ekyna_product.product_media.update';
    public const DELETE = 'ekyna_product.product_media.delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

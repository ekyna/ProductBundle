<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class ProductTranslationEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ProductTranslationEvents
{
    public const INSERT = 'ekyna_product.product_translation.insert';
    public const UPDATE = 'ekyna_product.product_translation.update';
    public const DELETE = 'ekyna_product.product_translation.delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

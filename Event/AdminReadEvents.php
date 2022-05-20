<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class AdminReadEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class AdminReadEvents
{
    public const PRODUCT  = 'ekyna_product.product.admin_read';
    public const CATEGORY = 'ekyna_product.category.admin_read';
    public const BRAND    = 'ekyna_product.brand.admin_read';
}

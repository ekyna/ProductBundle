<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class ProductEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ProductEvents
{
    public const INSERT                    = 'ekyna_product.product.insert';
    public const UPDATE                    = 'ekyna_product.product.update';
    public const DELETE                    = 'ekyna_product.product.delete';

    public const PRE_CREATE                = 'ekyna_product.product.pre_create';
    public const POST_CREATE               = 'ekyna_product.product.post_create';

    public const PRE_UPDATE                = 'ekyna_product.product.pre_update';
    public const POST_UPDATE               = 'ekyna_product.product.post_update';

    public const PRE_DELETE                = 'ekyna_product.product.pre_delete';
    public const POST_DELETE               = 'ekyna_product.product.post_delete';

    public const STOCK_UNIT_CHANGE         = 'ekyna_product.product.stock_unit_change';

    public const CHILD_PRICE_CHANGE        = 'ekyna_product.product.child_price_change';
    public const CHILD_AVAILABILITY_CHANGE = 'ekyna_product.product.child_availability_change';
    public const CHILD_STOCK_CHANGE        = 'ekyna_product.product.child_stock_change';

    public const PUBLIC_URL                = 'ekyna_product.product.public_url';
    public const IMAGE_URL                 = 'ekyna_product.product.image_url';
    public const SCHEMA_ORG                = 'ekyna_product.product.schema_org';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

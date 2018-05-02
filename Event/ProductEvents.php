<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class ProductEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ProductEvents
{
    const INSERT                    = 'ekyna_product.product.insert';
    const UPDATE                    = 'ekyna_product.product.update';
    const DELETE                    = 'ekyna_product.product.delete';

    const INITIALIZE                = 'ekyna_product.product.initialize';

    const PRE_CREATE                = 'ekyna_product.product.pre_create';
    const POST_CREATE               = 'ekyna_product.product.post_create';

    const PRE_UPDATE                = 'ekyna_product.product.pre_update';
    const POST_UPDATE               = 'ekyna_product.product.post_update';

    const PRE_DELETE                = 'ekyna_product.product.pre_delete';
    const POST_DELETE               = 'ekyna_product.product.post_delete';

    const STOCK_UNIT_CHANGE         = 'ekyna_product.product.stock_unit_change';

    const CHILD_PRICE_CHANGE        = 'ekyna_product.product.child_price_change';
    const CHILD_AVAILABILITY_CHANGE = 'ekyna_product.product.child_availability_change';
    const CHILD_STOCK_CHANGE        = 'ekyna_product.product.child_stock_change';

    const PUBLIC_URL                = 'ekyna_product.product.public_url';
}

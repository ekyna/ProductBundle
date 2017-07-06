<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class PricingEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PricingEvents
{
    const INSERT             = 'ekyna_product.pricing.insert';
    const UPDATE             = 'ekyna_product.pricing.update';
    const DELETE             = 'ekyna_product.pricing.delete';

    const PRE_CREATE         = 'ekyna_product.pricing.pre_create';
    const POST_CREATE        = 'ekyna_product.pricing.post_create';

    const PRE_UPDATE         = 'ekyna_product.pricing.pre_update';
    const POST_UPDATE        = 'ekyna_product.pricing.post_update';

    const PRE_DELETE         = 'ekyna_product.pricing.pre_delete';
    const POST_DELETE        = 'ekyna_product.pricing.post_delete';
}

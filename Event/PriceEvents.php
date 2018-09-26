<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class PriceEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PriceEvents
{
    const INSERT      = 'ekyna_product.price.insert';
    const UPDATE      = 'ekyna_product.price.update';
    const DELETE      = 'ekyna_product.price.delete';

    const INITIALIZE  = 'ekyna_product.price.initialize';

    const PRE_CREATE  = 'ekyna_product.price.pre_create';
    const POST_CREATE = 'ekyna_product.price.post_create';

    const PRE_UPDATE  = 'ekyna_product.price.pre_update';
    const POST_UPDATE = 'ekyna_product.price.post_update';

    const PRE_DELETE  = 'ekyna_product.price.pre_delete';
    const POST_DELETE = 'ekyna_product.price.post_delete';
}

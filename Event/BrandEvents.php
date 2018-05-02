<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class BrandEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class BrandEvents
{
    const INSERT      = 'ekyna_product.brand.insert';
    const UPDATE      = 'ekyna_product.brand.update';
    const DELETE      = 'ekyna_product.brand.delete';

    const INITIALIZE  = 'ekyna_product.brand.initialize';

    const PRE_CREATE  = 'ekyna_product.brand.pre_create';
    const POST_CREATE = 'ekyna_product.brand.post_create';

    const PRE_UPDATE  = 'ekyna_product.brand.pre_update';
    const POST_UPDATE = 'ekyna_product.brand.post_update';

    const PRE_DELETE  = 'ekyna_product.brand.pre_delete';
    const POST_DELETE = 'ekyna_product.brand.post_delete';

    const PUBLIC_URL  = 'ekyna_product.brand.public_url';
}

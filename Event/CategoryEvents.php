<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class CategoryEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CategoryEvents
{
    const INSERT      = 'ekyna_product.category.insert';
    const UPDATE      = 'ekyna_product.category.update';
    const DELETE      = 'ekyna_product.category.delete';

    const INITIALIZE  = 'ekyna_product.category.initialize';

    const PRE_CREATE  = 'ekyna_product.category.pre_create';
    const POST_CREATE = 'ekyna_product.category.post_create';

    const PRE_UPDATE  = 'ekyna_product.category.pre_update';
    const POST_UPDATE = 'ekyna_product.category.post_update';

    const PRE_DELETE  = 'ekyna_product.category.pre_delete';
    const POST_DELETE = 'ekyna_product.category.post_delete';

    const PUBLIC_URL  = 'ekyna_product.category.public_url';
}

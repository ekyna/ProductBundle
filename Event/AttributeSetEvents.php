<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class AttributeSetEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AttributeSetEvents
{
    const INSERT      = 'ekyna_product.attribute_set.insert';
    const UPDATE      = 'ekyna_product.attribute_set.update';
    const DELETE      = 'ekyna_product.attribute_set.delete';

    const INITIALIZE  = 'ekyna_product.attribute_set.initialize';

    const PRE_CREATE  = 'ekyna_product.attribute_set.pre_create';
    const POST_CREATE = 'ekyna_product.attribute_set.post_create';

    const PRE_UPDATE  = 'ekyna_product.attribute_set.pre_update';
    const POST_UPDATE = 'ekyna_product.attribute_set.post_update';

    const PRE_DELETE  = 'ekyna_product.attribute_set.pre_delete';
    const POST_DELETE = 'ekyna_product.attribute_set.post_delete';
}

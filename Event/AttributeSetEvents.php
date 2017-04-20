<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class AttributeSetEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class AttributeSetEvents
{
    public const INSERT      = 'ekyna_product.attribute_set.insert';
    public const UPDATE      = 'ekyna_product.attribute_set.update';
    public const DELETE      = 'ekyna_product.attribute_set.delete';

    public const PRE_CREATE  = 'ekyna_product.attribute_set.pre_create';
    public const POST_CREATE = 'ekyna_product.attribute_set.post_create';

    public const PRE_UPDATE  = 'ekyna_product.attribute_set.pre_update';
    public const POST_UPDATE = 'ekyna_product.attribute_set.post_update';

    public const PRE_DELETE  = 'ekyna_product.attribute_set.pre_delete';
    public const POST_DELETE = 'ekyna_product.attribute_set.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

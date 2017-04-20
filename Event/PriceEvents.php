<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class PriceEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PriceEvents
{
    public const INSERT      = 'ekyna_product.price.insert';
    public const UPDATE      = 'ekyna_product.price.update';
    public const DELETE      = 'ekyna_product.price.delete';

    public const PRE_CREATE  = 'ekyna_product.price.pre_create';
    public const POST_CREATE = 'ekyna_product.price.post_create';

    public const PRE_UPDATE  = 'ekyna_product.price.pre_update';
    public const POST_UPDATE = 'ekyna_product.price.post_update';

    public const PRE_DELETE  = 'ekyna_product.price.pre_delete';
    public const POST_DELETE = 'ekyna_product.price.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class PricingEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PricingEvents
{
    public const INSERT      = 'ekyna_product.pricing.insert';
    public const UPDATE      = 'ekyna_product.pricing.update';
    public const DELETE      = 'ekyna_product.pricing.delete';

    public const PRE_CREATE  = 'ekyna_product.pricing.pre_create';
    public const POST_CREATE = 'ekyna_product.pricing.post_create';

    public const PRE_UPDATE  = 'ekyna_product.pricing.pre_update';
    public const POST_UPDATE = 'ekyna_product.pricing.post_update';

    public const PRE_DELETE  = 'ekyna_product.pricing.pre_delete';
    public const POST_DELETE = 'ekyna_product.pricing.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

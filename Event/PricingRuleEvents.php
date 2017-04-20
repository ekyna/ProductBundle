<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class PricingRuleEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PricingRuleEvents
{
    public const INSERT      = 'ekyna_product.pricing_rule.insert';
    public const UPDATE      = 'ekyna_product.pricing_rule.update';
    public const DELETE      = 'ekyna_product.pricing_rule.delete';

    public const PRE_CREATE  = 'ekyna_product.pricing_rule.pre_create';
    public const POST_CREATE = 'ekyna_product.pricing_rule.post_create';

    public const PRE_UPDATE  = 'ekyna_product.pricing_rule.pre_update';
    public const POST_UPDATE = 'ekyna_product.pricing_rule.post_update';

    public const PRE_DELETE  = 'ekyna_product.pricing_rule.pre_delete';
    public const POST_DELETE = 'ekyna_product.pricing_rule.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

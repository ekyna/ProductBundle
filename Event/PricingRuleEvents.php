<?php

namespace Ekyna\Bundle\ProductBundle\Event;

/**
 * Class PricingRuleEvents
 * @package Ekyna\Bundle\ProductBundle\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PricingRuleEvents
{
    const INSERT      = 'ekyna_product.pricing_rule.insert';
    const UPDATE      = 'ekyna_product.pricing_rule.update';
    const DELETE      = 'ekyna_product.pricing_rule.delete';

    const INITIALIZE  = 'ekyna_product.pricing_rule.initialize';

    const PRE_CREATE  = 'ekyna_product.pricing_rule.pre_create';
    const POST_CREATE = 'ekyna_product.pricing_rule.post_create';

    const PRE_UPDATE  = 'ekyna_product.pricing_rule.pre_update';
    const POST_UPDATE = 'ekyna_product.pricing_rule.post_update';

    const PRE_DELETE  = 'ekyna_product.pricing_rule.pre_delete';
    const POST_DELETE = 'ekyna_product.pricing_rule.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\Pricing;
use Ekyna\Bundle\ProductBundle\Entity\PricingRule;
use PHPUnit\Framework\TestCase;

/**
 * Class PricingRuleTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingRuleTest extends TestCase
{
    public function test_setPricing_withPricing()
    {
        $rule = new PricingRule();
        $pricing = new Pricing();

        $rule->setPricing($pricing);

        $this->assertEquals($pricing, $rule->getPricing());
        $this->assertTrue($pricing->hasRule($rule));
    }

    public function test_setPricing_withNull()
    {
        $rule = new PricingRule();
        $pricing = new Pricing();

        $rule->setPricing($pricing);
        $rule->setPricing(null);

        $this->assertNull($rule->getPricing());
        $this->assertFalse($pricing->hasRule($rule));
    }

    public function test_setPricing_withAnotherPricing()
    {
        $rule = new PricingRule();
        $pricingA = new Pricing();
        $pricingB = new Pricing();

        $rule->setPricing($pricingA);
        $rule->setPricing($pricingB);

        $this->assertEquals($pricingB, $rule->getPricing());
        $this->assertTrue($pricingB->hasRule($rule));
        $this->assertFalse($pricingA->hasRule($rule));
    }
}
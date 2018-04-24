<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\Pricing;
use Ekyna\Bundle\ProductBundle\Entity\PricingRule;
use PHPUnit\Framework\TestCase;

/**
 * Class PricingTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingTest extends TestCase
{
    public function test_addRule()
    {
        $pricing = new Pricing();
        $rule = new PricingRule();

        $pricing->addRule($rule);

        $this->assertTrue($pricing->hasRule($rule));
        $this->assertEquals($pricing, $rule->getPricing());
    }

    public function test_removeRule()
    {
        $pricing = new Pricing();
        $rule = new PricingRule();

        $pricing->addRule($rule);
        $pricing->removeRule($rule);

        $this->assertFalse($pricing->hasRule($rule));
        $this->assertNull($rule->getPricing());
    }
}
<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\BundleChoice;
use Ekyna\Bundle\ProductBundle\Entity\BundleChoiceRule;
use PHPUnit\Framework\TestCase;

/**
 * Class BundleChoiceRuleTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceRuleTest extends TestCase
{
    public function test_setChoice_withChoice()
    {
        $rule = new BundleChoiceRule();
        $choice = new BundleChoice();

        $rule->setSlot($choice);

        $this->assertEquals($choice, $rule->getSlot());
        $this->assertTrue($choice->hasRule($rule));
    }

    public function test_setChoice_withNull()
    {
        $rule = new BundleChoiceRule();
        $choice = new BundleChoice();

        $rule->setSlot($choice);
        $rule->setSlot(null);

        $this->assertNull($rule->getSlot());
        $this->assertFalse($choice->hasRule($rule));
    }

    public function test_setChoice_withAnotherChoice()
    {
        $rule = new BundleChoiceRule();
        $choiceA = new BundleChoice();
        $choiceB = new BundleChoice();

        $rule->setSlot($choiceA);
        $rule->setSlot($choiceB);

        $this->assertEquals($choiceB, $rule->getSlot());
        $this->assertTrue($choiceB->hasRule($rule));
        $this->assertFalse($choiceA->hasRule($rule));
    }
}
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

        $rule->setChoice($choice);

        $this->assertEquals($choice, $rule->getChoice());
        $this->assertTrue($choice->hasRule($rule));
    }

    public function test_setChoice_withNull()
    {
        $rule = new BundleChoiceRule();
        $choice = new BundleChoice();

        $rule->setChoice($choice);
        $rule->setChoice(null);

        $this->assertNull($rule->getChoice());
        $this->assertFalse($choice->hasRule($rule));
    }

    public function test_setChoice_withAnotherChoice()
    {
        $rule = new BundleChoiceRule();
        $choiceA = new BundleChoice();
        $choiceB = new BundleChoice();

        $rule->setChoice($choiceA);
        $rule->setChoice($choiceB);

        $this->assertEquals($choiceB, $rule->getChoice());
        $this->assertTrue($choiceB->hasRule($rule));
        $this->assertFalse($choiceA->hasRule($rule));
    }
}
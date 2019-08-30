<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\BundleChoice;
use Ekyna\Bundle\ProductBundle\Entity\BundleChoiceRule;
use Ekyna\Bundle\ProductBundle\Entity\BundleSlot;
use PHPUnit\Framework\TestCase;

/**
 * Class BundleChoiceTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceTest extends TestCase
{
    public function test_setSlot_withSlot()
    {
        $choice = new BundleChoice();
        $slot = new BundleSlot();

        $choice->setSlot($slot);

        $this->assertEquals($slot, $choice->getSlot());
        $this->assertTrue($slot->hasChoice($choice));
    }

    public function test_setSlot_withNull()
    {
        $choice = new BundleChoice();
        $slot = new BundleSlot();

        $choice->setSlot($slot);
        $choice->setSlot(null);

        $this->assertNull($choice->getSlot());
        $this->assertFalse($slot->hasChoice($choice));
    }

    public function test_setSlot_withAnotherSlot()
    {
        $choice = new BundleChoice();
        $slotA = new BundleSlot();
        $slotB = new BundleSlot();

        $choice->setSlot($slotA);
        $choice->setSlot($slotB);

        $this->assertEquals($slotB, $choice->getSlot());
        $this->assertTrue($slotB->hasChoice($choice));
        $this->assertFalse($slotA->hasChoice($choice));
    }
    
    public function test_addRule()
    {
        $choice = new BundleChoice();
        $rule = new BundleChoiceRule();

        $choice->addRule($rule);

        $this->assertTrue($choice->hasRule($rule));
        $this->assertEquals($choice, $rule->getChoice());
    }

    public function test_removeRule()
    {
        $choice = new BundleChoice();
        $rule = new BundleChoiceRule();

        $choice->addRule($rule);
        $choice->removeRule($rule);

        $this->assertFalse($choice->hasRule($rule));
        $this->assertNull($rule->getChoice());
    }
}
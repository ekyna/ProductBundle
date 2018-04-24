<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\AttributeSet;
use Ekyna\Bundle\ProductBundle\Entity\AttributeSlot;
use PHPUnit\Framework\TestCase;

/**
 * Class AttributeSlotTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeSlotTest extends TestCase
{
    public function test_setSet_withSet()
    {
        $slot = new AttributeSlot();
        $set = new AttributeSet();

        $slot->setSet($set);

        $this->assertEquals($set, $slot->getSet());
        $this->assertTrue($set->hasSlot($slot));
    }

    public function test_setSet_withNull()
    {
        $slot = new AttributeSlot();
        $set = new AttributeSet();

        $slot->setSet($set);
        $slot->setSet(null);

        $this->assertNull($slot->getSet());
        $this->assertFalse($set->hasSlot($slot));
    }

    public function test_setSet_withAnotherSet()
    {
        $slot = new AttributeSlot();
        $setA = new AttributeSet();
        $setB = new AttributeSet();

        $slot->setSet($setA);
        $slot->setSet($setB);

        $this->assertEquals($setB, $slot->getSet());
        $this->assertTrue($setB->hasSlot($slot));
        $this->assertFalse($setA->hasSlot($slot));
    }
}
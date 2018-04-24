<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\AttributeSet;
use Ekyna\Bundle\ProductBundle\Entity\AttributeSlot;
use PHPUnit\Framework\TestCase;

/**
 * Class AttributeSetTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeSetTest extends TestCase
{
    public function test_addSlot()
    {
        $set = new AttributeSet();
        $slot = new AttributeSlot();

        $set->addSlot($slot);

        $this->assertTrue($set->hasSlot($slot));
        $this->assertEquals($set, $slot->getSet());
    }

    public function test_removeSlot()
    {
        $set = new AttributeSet();
        $slot = new AttributeSlot();

        $set->addSlot($slot);
        $set->removeSlot($slot);

        $this->assertFalse($set->hasSlot($slot));
        $this->assertNull($slot->getSet());
    }
}
<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\BundleChoice;
use Ekyna\Bundle\ProductBundle\Entity\BundleSlot;
use Ekyna\Bundle\ProductBundle\Entity\Product;
use PHPUnit\Framework\TestCase;

/**
 * Class BundleSlotTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotTest extends TestCase
{
    public function test_setBundle_withBundle()
    {
        $slot = new BundleSlot();
        $bundle = new Product();

        $slot->setBundle($bundle);

        $this->assertEquals($bundle, $slot->getBundle());
        $this->assertTrue($bundle->hasBundleSlot($slot));
    }

    public function test_setBundle_withNull()
    {
        $slot = new BundleSlot();
        $bundle = new Product();

        $slot->setBundle($bundle);
        $slot->setBundle(null);

        $this->assertNull($slot->getBundle());
        $this->assertFalse($bundle->hasBundleSlot($slot));
    }

    public function test_setBundle_withAnotherBundle()
    {
        $slot = new BundleSlot();
        $bundleA = new Product();
        $bundleB = new Product();

        $slot->setBundle($bundleA);
        $slot->setBundle($bundleB);

        $this->assertEquals($bundleB, $slot->getBundle());
        $this->assertTrue($bundleB->hasBundleSlot($slot));
        $this->assertFalse($bundleA->hasBundleSlot($slot));
    }

    public function test_addChoice()
    {
        $slot = new BundleSlot();
        $choice = new BundleChoice();

        $slot->addChoice($choice);

        $this->assertTrue($slot->hasChoice($choice));
        $this->assertEquals($slot, $choice->getSlot());
    }

    public function test_removeChoice()
    {
        $slot = new BundleSlot();
        $choice = new BundleChoice();

        $slot->addChoice($choice);
        $slot->removeChoice($choice);

        $this->assertFalse($slot->hasChoice($choice));
        $this->assertNull($choice->getSlot());
    }
}
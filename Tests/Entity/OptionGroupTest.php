<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\Option;
use Ekyna\Bundle\ProductBundle\Entity\OptionGroup;
use Ekyna\Bundle\ProductBundle\Entity\Product;
use PHPUnit\Framework\TestCase;

/**
 * Class OptionGroupTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionGroupTest extends TestCase
{
    public function test_setProduct_withProduct()
    {
        $group = new OptionGroup();
        $product = new Product();

        $group->setProduct($product);

        $this->assertEquals($product, $group->getProduct());
        $this->assertTrue($product->hasOptionGroup($group));
    }

    public function test_setProduct_withNull()
    {
        $group = new OptionGroup();
        $product = new Product();

        $group->setProduct($product);
        $group->setProduct(null);

        $this->assertNull($group->getProduct());
        $this->assertFalse($product->hasOptionGroup($group));
    }

    public function test_setProduct_withAnotherProduct()
    {
        $group = new OptionGroup();
        $productA = new Product();
        $productB = new Product();

        $group->setProduct($productA);
        $group->setProduct($productB);

        $this->assertEquals($productB, $group->getProduct());
        $this->assertTrue($productB->hasOptionGroup($group));
        $this->assertFalse($productA->hasOptionGroup($group));
    }

    public function test_addOption()
    {
        $group = new OptionGroup();
        $option = new Option();

        $group->addOption($option);

        $this->assertTrue($group->hasOption($option));
        $this->assertEquals($group, $option->getGroup());
    }

    public function test_removeOption()
    {
        $group = new OptionGroup();
        $option = new Option();

        $group->addOption($option);
        $group->removeOption($option);

        $this->assertFalse($group->hasOption($option));
        $this->assertNull($option->getGroup());
    }
}
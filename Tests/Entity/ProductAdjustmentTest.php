<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\Product;
use Ekyna\Bundle\ProductBundle\Entity\ProductAdjustment;
use PHPUnit\Framework\TestCase;

/**
 * Class ProductAdjustmentTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAdjustmentTest extends TestCase
{
    public function test_setProduct_withProduct()
    {
        $adjustment = new ProductAdjustment();
        $product = new Product();

        $adjustment->setProduct($product);

        $this->assertEquals($product, $adjustment->getProduct());
        $this->assertTrue($product->hasAdjustment($adjustment));
    }

    public function test_setProduct_withNull()
    {
        $adjustment = new ProductAdjustment();
        $product = new Product();

        $adjustment->setProduct($product);
        $adjustment->setProduct(null);

        $this->assertNull($adjustment->getProduct());
        $this->assertFalse($product->hasAdjustment($adjustment));
    }

    public function test_setProduct_withAnotherProduct()
    {
        $adjustment = new ProductAdjustment();
        $productA = new Product();
        $productB = new Product();

        $adjustment->setProduct($productA);
        $adjustment->setProduct($productB);

        $this->assertEquals($productB, $adjustment->getProduct());
        $this->assertTrue($productB->hasAdjustment($adjustment));
        $this->assertFalse($productA->hasAdjustment($adjustment));
    }
}
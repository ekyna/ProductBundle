<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\Product;
use Ekyna\Bundle\ProductBundle\Entity\ProductAttribute;
use PHPUnit\Framework\TestCase;

/**
 * Class ProductAttributeTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAttributeTest extends TestCase
{
    public function test_setProduct_withProduct()
    {
        $attribute = new ProductAttribute();
        $product = new Product();

        $attribute->setProduct($product);

        $this->assertEquals($product, $attribute->getProduct());
        $this->assertTrue($product->hasAttribute($attribute));
    }

    public function test_setProduct_withNull()
    {
        $attribute = new ProductAttribute();
        $product = new Product();

        $attribute->setProduct($product);
        $attribute->setProduct(null);

        $this->assertNull($attribute->getProduct());
        $this->assertFalse($product->hasAttribute($attribute));
    }

    public function test_setProduct_withAnotherProduct()
    {
        $attribute = new ProductAttribute();
        $productA = new Product();
        $productB = new Product();

        $attribute->setProduct($productA);
        $attribute->setProduct($productB);

        $this->assertEquals($productB, $attribute->getProduct());
        $this->assertTrue($productB->hasAttribute($attribute));
        $this->assertFalse($productA->hasAttribute($attribute));
    }
}
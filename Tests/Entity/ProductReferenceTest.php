<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\Product;
use Ekyna\Bundle\ProductBundle\Entity\ProductReference;
use PHPUnit\Framework\TestCase;

/**
 * Class ProductReferenceTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductReferenceTest extends TestCase
{
    public function test_setProduct_withProduct()
    {
        $reference = new ProductReference();
        $product = new Product();

        $reference->setProduct($product);

        $this->assertEquals($product, $reference->getProduct());
        $this->assertTrue($product->hasReference($reference));
    }

    public function test_setProduct_withNull()
    {
        $reference = new ProductReference();
        $product = new Product();

        $reference->setProduct($product);
        $reference->setProduct(null);

        $this->assertNull($reference->getProduct());
        $this->assertFalse($product->hasReference($reference));
    }

    public function test_setProduct_withAnotherProduct()
    {
        $reference = new ProductReference();
        $productA = new Product();
        $productB = new Product();

        $reference->setProduct($productA);
        $reference->setProduct($productB);

        $this->assertEquals($productB, $reference->getProduct());
        $this->assertTrue($productB->hasReference($reference));
        $this->assertFalse($productA->hasReference($reference));
    }
}
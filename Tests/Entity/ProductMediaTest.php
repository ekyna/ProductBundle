<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\Product;
use Ekyna\Bundle\ProductBundle\Entity\ProductMedia;
use PHPUnit\Framework\TestCase;

/**
 * Class ProductMediaTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductMediaTest extends TestCase
{
    public function test_setProduct_withProduct()
    {
        $media = new ProductMedia();
        $product = new Product();

        $media->setProduct($product);

        $this->assertEquals($product, $media->getProduct());
        $this->assertTrue($product->hasMedia($media));
    }

    public function test_setProduct_withNull()
    {
        $media = new ProductMedia();
        $product = new Product();

        $media->setProduct($product);
        $media->setProduct(null);

        $this->assertNull($media->getProduct());
        $this->assertFalse($product->hasMedia($media));
    }

    public function test_setProduct_withAnotherProduct()
    {
        $media = new ProductMedia();
        $productA = new Product();
        $productB = new Product();

        $media->setProduct($productA);
        $media->setProduct($productB);

        $this->assertEquals($productB, $media->getProduct());
        $this->assertTrue($productB->hasMedia($media));
        $this->assertFalse($productA->hasMedia($media));
    }
}
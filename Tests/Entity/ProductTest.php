<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\BundleSlot;
use Ekyna\Bundle\ProductBundle\Entity\OptionGroup;
use Ekyna\Bundle\ProductBundle\Entity\Product;
use Ekyna\Bundle\ProductBundle\Entity\ProductAdjustment;
use Ekyna\Bundle\ProductBundle\Entity\ProductAttribute;
use Ekyna\Bundle\ProductBundle\Entity\ProductMedia;
use Ekyna\Bundle\ProductBundle\Entity\ProductReference;
use PHPUnit\Framework\TestCase;

/**
 * Class ProductTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductTest extends TestCase
{
    public function test_setParent_withParent()
    {
        $variant = new Product();
        $parent = new Product();

        $variant->setParent($parent);

        $this->assertEquals($parent, $variant->getParent());
        $this->assertTrue($parent->hasVariant($variant));
    }

    public function test_setParent_withNull()
    {
        $variant = new Product();
        $parent = new Product();

        $variant->setParent($parent);
        $variant->setParent(null);

        $this->assertNull($variant->getParent());
        $this->assertFalse($parent->hasVariant($variant));
    }

    public function test_setParent_withAnotherParent()
    {
        $variant = new Product();
        $parentA = new Product();
        $parentB = new Product();

        $variant->setParent($parentA);
        $variant->setParent($parentB);

        $this->assertEquals($parentB, $variant->getParent());
        $this->assertTrue($parentB->hasVariant($variant));
        $this->assertFalse($parentA->hasVariant($variant));
    }

    public function test_addAttribute()
    {
        $product = new Product();
        $attribute = new ProductAttribute();

        $product->addAttribute($attribute);

        $this->assertTrue($product->hasAttribute($attribute));
        $this->assertEquals($product, $attribute->getProduct());
    }

    public function test_removeAttribute()
    {
        $product = new Product();
        $attribute = new ProductAttribute();

        $product->addAttribute($attribute);
        $product->removeAttribute($attribute);

        $this->assertFalse($product->hasAttribute($attribute));
        $this->assertNull($attribute->getProduct());
    }

    public function test_addAdjustment()
    {
        $product = new Product();
        $adjustment = new ProductAdjustment();

        $product->addAdjustment($adjustment);

        $this->assertTrue($product->hasAdjustment($adjustment));
        $this->assertEquals($product, $adjustment->getProduct());
    }

    public function test_removeAdjustment()
    {
        $product = new Product();
        $adjustment = new ProductAdjustment();

        $product->addAdjustment($adjustment);
        $product->removeAdjustment($adjustment);

        $this->assertFalse($product->hasAdjustment($adjustment));
        $this->assertNull($adjustment->getProduct());
    }

    public function test_addOptionGroup()
    {
        $product = new Product();
        $optionGroup = new OptionGroup();

        $product->addOptionGroup($optionGroup);

        $this->assertTrue($product->hasOptionGroup($optionGroup));
        $this->assertEquals($product, $optionGroup->getProduct());
    }

    public function test_removeOptionGroup()
    {
        $product = new Product();
        $optionGroup = new OptionGroup();

        $product->addOptionGroup($optionGroup);
        $product->removeOptionGroup($optionGroup);

        $this->assertFalse($product->hasOptionGroup($optionGroup));
        $this->assertNull($optionGroup->getProduct());
    }

    public function test_addBundleSlot()
    {
        $product = new Product();
        $bundleSlot = new BundleSlot();

        $product->addBundleSlot($bundleSlot);

        $this->assertTrue($product->hasBundleSlot($bundleSlot));
        $this->assertEquals($product, $bundleSlot->getBundle());
    }

    public function test_removeBundleSlot()
    {
        $product = new Product();
        $bundleSlot = new BundleSlot();

        $product->addBundleSlot($bundleSlot);
        $product->removeBundleSlot($bundleSlot);

        $this->assertFalse($product->hasBundleSlot($bundleSlot));
        $this->assertNull($bundleSlot->getBundle());
    }

    public function test_addMedia()
    {
        $product = new Product();
        $media = new ProductMedia();

        $product->addMedia($media);

        $this->assertTrue($product->hasMedia($media));
        $this->assertEquals($product, $media->getProduct());
    }

    public function test_removeMedia()
    {
        $product = new Product();
        $media = new ProductMedia();

        $product->addMedia($media);
        $product->removeMedia($media);

        $this->assertFalse($product->hasMedia($media));
        $this->assertNull($media->getProduct());
    }

    public function test_addReference()
    {
        $product = new Product();
        $reference = new ProductReference();

        $product->addReference($reference);

        $this->assertTrue($product->hasReference($reference));
        $this->assertEquals($product, $reference->getProduct());
    }

    public function test_removeReference()
    {
        $product = new Product();
        $reference = new ProductReference();

        $product->addReference($reference);
        $product->removeReference($reference);

        $this->assertFalse($product->hasReference($reference));
        $this->assertNull($reference->getProduct());
    }
}
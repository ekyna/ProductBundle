<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\Attribute;
use Ekyna\Bundle\ProductBundle\Entity\AttributeChoice;
use PHPUnit\Framework\TestCase;

/**
 * Class AttributeChoiceTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeChoiceTest extends TestCase
{
    public function test_setAttribute_withAttribute()
    {
        $choice = new AttributeChoice();
        $attribute = new Attribute();

        $choice->setAttribute($attribute);

        $this->assertEquals($attribute, $choice->getAttribute());
        $this->assertTrue($attribute->hasChoice($choice));
    }

    public function test_setAttribute_withNull()
    {
        $choice = new AttributeChoice();
        $attribute = new Attribute();

        $choice->setAttribute($attribute);
        $choice->setAttribute(null);

        $this->assertNull($choice->getAttribute());
        $this->assertFalse($attribute->hasChoice($choice));
    }

    public function test_setAttribute_withAnotherAttribute()
    {
        $choice = new AttributeChoice();
        $attributeA = new Attribute();
        $attributeB = new Attribute();

        $choice->setAttribute($attributeA);
        $choice->setAttribute($attributeB);

        $this->assertEquals($attributeB, $choice->getAttribute());
        $this->assertTrue($attributeB->hasChoice($choice));
        $this->assertFalse($attributeA->hasChoice($choice));
    }
}
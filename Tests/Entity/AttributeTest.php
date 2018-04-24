<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\Attribute;
use Ekyna\Bundle\ProductBundle\Entity\AttributeChoice;
use PHPUnit\Framework\TestCase;

/**
 * Class AttributeTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeTest extends TestCase
{
    public function test_addChoice()
    {
        $attribute = new Attribute();
        $choice = new AttributeChoice();

        $attribute->addChoice($choice);

        $this->assertTrue($attribute->hasChoice($choice));
        $this->assertEquals($attribute, $choice->getAttribute());
    }

    public function test_removeChoice()
    {
        $attribute = new Attribute();
        $choice = new AttributeChoice();

        $attribute->addChoice($choice);
        $attribute->removeChoice($choice);

        $this->assertFalse($attribute->hasChoice($choice));
        $this->assertNull($choice->getAttribute());
    }
}
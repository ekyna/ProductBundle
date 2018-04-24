<?php

namespace Ekyna\Bundle\ProductBundle\Tests\Entity;

use Ekyna\Bundle\ProductBundle\Entity\Option;
use Ekyna\Bundle\ProductBundle\Entity\OptionGroup;
use PHPUnit\Framework\TestCase;

/**
 * Class OptionTest
 * @package Ekyna\Bundle\ProductBundle\Tests\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionTest extends TestCase
{
    public function test_setGroup_withGroup()
    {
        $option = new Option();
        $group = new OptionGroup();

        $option->setGroup($group);

        $this->assertEquals($group, $option->getGroup());
        $this->assertTrue($group->hasOption($option));
    }

    public function test_setGroup_withNull()
    {
        $option = new Option();
        $group = new OptionGroup();

        $option->setGroup($group);
        $option->setGroup(null);

        $this->assertNull($option->getGroup());
        $this->assertFalse($group->hasOption($option));
    }

    public function test_setGroup_withAnotherGroup()
    {
        $option = new Option();
        $groupA = new OptionGroup();
        $groupB = new OptionGroup();

        $option->setGroup($groupA);
        $option->setGroup($groupB);

        $this->assertEquals($groupB, $option->getGroup());
        $this->assertTrue($groupB->hasOption($option));
        $this->assertFalse($groupA->hasOption($option));
    }
}
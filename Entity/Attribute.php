<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model;

/**
 * Class Attribute
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Attribute implements Model\AttributeInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Model\AttributeGroupInterface
     */
    protected $group;

    /**
     * @var string
     */
    protected $name;


    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @inheritdoc
     */
    public function setGroup(Model\AttributeGroupInterface $group)
    {
        $this->group = $group;
        $group->addAttribute($this);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}

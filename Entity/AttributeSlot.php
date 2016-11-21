<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model;

/**
 * Class AttributeSlot
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeSlot implements Model\AttributeSlotInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\AttributeSetInterface
     */
    protected $set;

    /**
     * @var Model\AttributeGroupInterface
     */
    protected $group;

    /**
     * @var bool
     */
    protected $multiple = false;

    /**
     * @var bool
     */
    protected $required = true;

    /**
     * @var integer
     */
    protected $position;


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
    public function getSet()
    {
        return $this->set;
    }

    /**
     * @inheritdoc
     */
    public function setSet(Model\AttributeSetInterface $set = null)
    {
        $this->set = $set;
        $set->addSlot($this);

        return $this;
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

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * @inheritdoc
     */
    public function setMultiple($multiple)
    {
        $this->multiple = (bool)$multiple;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @inheritdoc
     */
    public function setRequired($required)
    {
        $this->required = (bool)$required;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }
}

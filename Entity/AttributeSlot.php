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
     * @var Model\AttributeInterface
     */
    protected $attribute;

    /**
     * @var bool
     */
    protected $required = false;

    /**
     * @var bool
     */
    protected $naming = false;

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
        if ($this->set !== $set) {
            if ($previous = $this->set) {
                $this->set = null;
                $previous->removeSlot($this);
            }

            if ($this->set = $set) {
                $this->set->addSlot($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @inheritdoc
     */
    public function setAttribute(Model\AttributeInterface $attribute)
    {
        $this->attribute = $attribute;

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
    public function isNaming()
    {
        return $this->naming;
    }

    /**
     * @inheritdoc
     */
    public function setNaming($naming)
    {
        $this->naming = (bool)$naming;

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

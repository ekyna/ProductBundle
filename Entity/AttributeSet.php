<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\ProductBundle\Model;

/**
 * Class AttributeSet
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AttributeSet implements Model\AttributeSetInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ArrayCollection|Model\AttributeSlotInterface[]
     */
    protected $slots;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->slots = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
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

    /**
     * @inheritdoc
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @inheritdoc
     */
    public function hasSlot(Model\AttributeSlotInterface $slot)
    {
        return $this->slots->contains($slot);
    }

    /**
     * @inheritdoc
     */
    public function addSlot(Model\AttributeSlotInterface $slot)
    {
        if (!$this->hasSlot($slot)) {
            if ($slot->getSet() !== $this) {
                $slot->setSet($this);
            }
            $this->slots->add($slot);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeSlot(Model\AttributeSlotInterface $slot)
    {
        if ($this->hasSlot($slot)) {
            $slot->setSet(null);
            $this->slots->removeElement($slot);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSlots(ArrayCollection $slots)
    {
        $this->slots = $slots;

        return $this;
    }
}

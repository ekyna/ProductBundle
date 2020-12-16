<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\ProductBundle\Model\AttributeChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\AttributeSlotInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductAttributeInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;

/**
 * Class ProductAttribute
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAttribute implements ProductAttributeInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * @var AttributeSlotInterface
     */
    protected $attributeSlot;

    /**
     * @var ArrayCollection|AttributeChoiceInterface[]
     */
    protected $choices;

    /**
     * @var mixed
     */
    protected $value;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->choices = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @inheritdoc
     */
    public function setProduct(ProductInterface $product = null)
    {
        if ($this->product !== $product) {
            if ($previous = $this->product) {
                $this->product = null;
                $previous->removeAttribute($this);
            }

            if ($this->product = $product) {
                $this->product->addAttribute($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeSlot()
    {
        return $this->attributeSlot;
    }

    /**
     * @inheritdoc
     */
    public function setAttributeSlot(AttributeSlotInterface $attributeSlot)
    {
        $this->attributeSlot = $attributeSlot;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @inheritdoc
     */
    public function hasChoice(AttributeChoiceInterface $choice)
    {
        return $this->choices->contains($choice);
    }

    /**
     * @inheritdoc
     */
    public function addChoice(AttributeChoiceInterface $choice)
    {
        if (!$this->hasChoice($choice)) {
            $this->choices->add($choice);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeChoice(AttributeChoiceInterface $choice)
    {
        if ($this->hasChoice($choice)) {
            $this->choices->removeElement($choice);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty()
    {
        return is_null($this->value) && 0 === $this->choices->count();
    }
}

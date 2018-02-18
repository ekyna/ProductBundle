<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface ProductAttributeInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductAttributeInterface extends ResourceInterface
{
    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * Sets the product.
     *
     * @param ProductInterface $product
     *
     * @return $this|ProductAttributeInterface
     */
    public function setProduct(ProductInterface $product = null);

    /**
     * Returns the attribute slot.
     *
     * @return AttributeSlotInterface
     */
    public function getAttributeSlot();

    /**
     * Sets the attribute slot.
     *
     * @param AttributeSlotInterface $attribute
     *
     * @return $this|ProductAttributeInterface
     */
    public function setAttributeSlot(AttributeSlotInterface $attribute);

    /**
     * Returns the choices.
     *
     * @return ArrayCollection|AttributeChoiceInterface[]
     */
    public function getChoices();

    /**
     * Returns whether this product attribute has the given attribute choice.
     *
     * @param AttributeChoiceInterface $choice
     *
     * @return bool
     */
    public function hasChoice(AttributeChoiceInterface $choice);

    /**
     * Adds the given attribute choice.
     *
     * @param AttributeChoiceInterface $choice
     *
     * @return $this|ProductAttributeInterface
     */
    public function addChoice(AttributeChoiceInterface $choice);

    /**
     * Removes the given attribute choice.
     *
     * @param AttributeChoiceInterface $choice
     *
     * @return $this|ProductAttributeInterface
     */
    public function removeChoice(AttributeChoiceInterface $choice);

    /**
     * Returns the value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Sets the value.
     *
     * @param mixed $value
     *
     * @return $this|ProductAttributeInterface
     */
    public function setValue($value);

    /**
     * Returns whether or not the product attribute has no value and no choices.
     *
     * @return bool
     */
    public function isEmpty();
}
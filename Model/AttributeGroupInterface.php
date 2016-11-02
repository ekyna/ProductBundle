<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface AttributeGroupInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AttributeGroupInterface extends ResourceInterface
{
    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|AttributeGroupInterface
     */
    public function setName($name);

    /**
     * Returns the attributes.
     *
     * @return ArrayCollection|AttributeInterface[]
     */
    public function getAttributes();

    /**
     * Returns whether the group has the attribute or not.
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function hasAttribute(AttributeInterface $attribute);

    /**
     * Adds the attribute.
     *
     * @param AttributeInterface $attribute
     *
     * @return $this|OptionGroupInterface
     */
    public function addAttribute(AttributeInterface $attribute);

    /**
     * Removes the attribute.
     *
     * @param AttributeInterface $attribute
     *
     * @return $this|OptionGroupInterface
     */
    public function removeAttribute(AttributeInterface $attribute);

    /**
     * Sets the attributes.
     *
     * @param ArrayCollection|AttributeInterface[] $attributes
     *
     * @return $this|OptionGroupInterface
     * @internal
     */
    public function setAttributes(ArrayCollection $attributes);
}

<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Comparable;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface AttributeSetInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AttributeSetInterface extends ResourceInterface, Comparable
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
     * @return $this|AttributeSetInterface
     */
    public function setName($name);

    /**
     * Returns the slot.
     *
     * @return ArrayCollection|AttributeSlotInterface[]
     */
    public function getSlots();

    /**
     * Returns whether the set has the slot or not.
     *
     * @param AttributeSlotInterface $slot
     *
     * @return bool
     */
    public function hasSlot(AttributeSlotInterface $slot);

    /**
     * Adds the slot.
     *
     * @param AttributeSlotInterface $slot
     *
     * @return $this|AttributeSetInterface
     */
    public function addSlot(AttributeSlotInterface $slot);

    /**
     * Removes the slot.
     *
     * @param AttributeSlotInterface $slot
     *
     * @return $this|AttributeSetInterface
     */
    public function removeSlot(AttributeSlotInterface $slot);

    /**
     * Sets the slots.
     *
     * @param ArrayCollection|AttributeSlotInterface[] $slots
     *
     * @return $this|AttributeSetInterface
     * @internal
     */
    public function setSlots(ArrayCollection $slots);

    /**
     * Returns whether this attribute set has at least one required slot.
     *
     * @return bool
     */
    public function hasRequiredSlot();

    /**
     * Returns whether this attribute set has at least one naming slot.
     *
     * @return bool
     */
    public function hasNamingSlot();
}

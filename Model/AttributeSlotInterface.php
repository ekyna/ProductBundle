<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Model\SortableInterface;

/**
 * Interface AttributeSlotInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AttributeSlotInterface extends ResourceInterface, SortableInterface
{
    /**
     * Returns the set.
     *
     * @return AttributeSetInterface
     */
    public function getSet();

    /**
     * Sets the set.
     *
     * @param AttributeSetInterface $set
     *
     * @return $this|AttributeSlotInterface
     */
    public function setSet(AttributeSetInterface $set = null);

    /**
     * Returns the attribute.
     *
     * @return AttributeInterface
     */
    public function getAttribute();

    /**
     * Sets the attribute.
     *
     * @param AttributeInterface $attribute
     *
     * @return $this|AttributeSlotInterface
     */
    public function setAttribute(AttributeInterface $attribute);

    /**
     * Returns whether this slot's attribute is required.
     *
     * @return boolean
     */
    public function isRequired();

    /**
     * Sets whether this slot's attribute is required.
     *
     * @param boolean $required
     *
     * @return $this|AttributeSlotInterface
     */
    public function setRequired($required);

    /**
     * Returns whether this slot's attribute is used to generate variant names and designations.
     *
     * @return bool
     */
    public function isNaming();

    /**
     * Sets whether this slot's attribute is used to generate variant names and designations.
     *
     * @param bool $naming
     *
     * @return $this|AttributeSlotInterface
     */
    public function setNaming($naming);
}

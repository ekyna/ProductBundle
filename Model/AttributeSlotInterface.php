<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface AttributeSlotInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AttributeSlotInterface extends ResourceInterface
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
     * Returns the group.
     *
     * @return AttributeGroupInterface
     */
    public function getGroup();

    /**
     * Sets the group.
     *
     * @param AttributeGroupInterface $group
     *
     * @return $this|AttributeSlotInterface
     */
    public function setGroup(AttributeGroupInterface $group);

    /**
     * Returns the multiple.
     *
     * @return boolean
     */
    public function isMultiple();

    /**
     * Sets the multiple.
     *
     * @param boolean $multiple
     *
     * @return $this|AttributeSlotInterface
     */
    public function setMultiple($multiple);

    /**
     * Returns the position.
     *
     * @return int
     */
    public function getPosition();

    /**
     * Sets the position.
     *
     * @param int $position
     *
     * @return $this|AttributeSlotInterface
     */
    public function setPosition($position);
}

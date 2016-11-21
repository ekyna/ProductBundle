<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\MediaBundle\Model\MediaSubjectInterface;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface AttributeInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method AttributeTranslationInterface translate($locale = null, $create = false)
 */
interface AttributeInterface extends MediaSubjectInterface, RM\SortableInterface, RM\ResourceInterface
{
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
     * @return $this|AttributeInterface
     */
    public function setGroup(AttributeGroupInterface $group);

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
     * @return $this|AttributeInterface
     */
    public function setName($name);

    /**
     * Returns the color.
     *
     * @return string
     */
    public function getColor();

    /**
     * Sets the color.
     *
     * @param string $color
     *
     * @return $this|AttributeInterface
     */
    public function setColor($color);

    /**
     * Returns the (translated) title.
     *
     * @return string
     */
    public function getTitle();
}

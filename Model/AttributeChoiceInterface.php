<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Bundle\MediaBundle\Model\MediaSubjectInterface;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface AttributeChoiceInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method AttributeChoiceTranslationInterface translate($locale = null, $create = false)
 */
interface AttributeChoiceInterface extends MediaSubjectInterface, RM\SortableInterface, RM\TranslatableInterface
{
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
     * @return $this|AttributeChoiceInterface
     */
    public function setAttribute(AttributeInterface $attribute = null);

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
     * @return $this|AttributeChoiceInterface
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
     * @return $this|AttributeChoiceInterface
     */
    public function setColor($color);

    /**
     * Returns the (translated) title.
     *
     * @return string
     */
    public function getTitle();
}

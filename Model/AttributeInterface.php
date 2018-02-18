<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface AttributeInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method AttributeTranslationInterface translate($locale = null, $create = false)
 */
interface AttributeInterface extends RM\SortableInterface, RM\TranslatableInterface
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
     * @return $this|AttributeInterface
     */
    public function setName($name);

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType();

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return $this|AttributeInterface
     */
    public function setType($type);

    /**
     * Returns the configuration.
     *
     * @return array
     */
    public function getConfig();

    /**
     * Sets the configuration.
     *
     * @param array $configuration
     *
     * @return $this|AttributeInterface
     */
    public function setConfig(array $configuration);

    /**
     * Returns the (translated) title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Returns the attributes.
     *
     * @return ArrayCollection|AttributeChoiceInterface[]
     */
    public function getChoices();

    /**
     * Returns whether the group has the attribute or not.
     *
     * @param AttributeChoiceInterface $choice
     *
     * @return bool
     */
    public function hasChoice(AttributeChoiceInterface $choice);

    /**
     * Adds the attribute.
     *
     * @param AttributeChoiceInterface $choice
     *
     * @return $this|OptionGroupInterface
     */
    public function addChoice(AttributeChoiceInterface $choice);

    /**
     * Removes the attribute.
     *
     * @param AttributeChoiceInterface $choice
     *
     * @return $this|OptionGroupInterface
     */
    public function removeChoice(AttributeChoiceInterface $choice);

    /**
     * Sets the attributes.
     *
     * @param ArrayCollection|AttributeChoiceInterface[] $attributes
     *
     * @return $this|OptionGroupInterface
     * @internal
     */
    public function setChoices(ArrayCollection $attributes);
}

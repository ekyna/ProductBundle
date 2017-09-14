<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\MediaBundle\Model\MediaSubjectInterface;
use Ekyna\Component\Resource\Model\TranslatableInterface;

/**
 * Interface BundleSlotInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method BundleSlotTranslationInterface translate($locale = null, $create = false)
 */
interface BundleSlotInterface extends TranslatableInterface, MediaSubjectInterface
{
    /**
     * Returns the bundle.
     *
     * @return ProductInterface
     */
    public function getBundle();

    /**
     * Sets the bundle.
     *
     * @param ProductInterface $bundle
     *
     * @return $this|BundleSlotInterface
     */
    public function setBundle(ProductInterface $bundle = null);

    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Sets the title.
     *
     * @param string $title
     *
     * @return $this|BundleSlotInterface
     */
    //public function setTitle($title);

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return $this|BundleSlotInterface
     */
    //public function setDescription($description);

    /**
     * Returns the choices.
     *
     * @return ArrayCollection|BundleChoiceInterface[]
     */
    public function getChoices();

    /**
     * Returns whether the slot has the choice or not.
     *
     * @param BundleChoiceInterface $choice
     *
     * @return bool
     */
    public function hasChoice(BundleChoiceInterface $choice);

    /**
     * Adds the choice.
     *
     * @param BundleChoiceInterface $choice
     *
     * @return $this|BundleSlotInterface
     */
    public function addChoice(BundleChoiceInterface $choice);

    /**
     * Removes the choice.
     *
     * @param BundleChoiceInterface $choice
     *
     * @return $this|BundleSlotInterface
     */
    public function removeChoice(BundleChoiceInterface $choice);

    /**
     * Sets the choices.
     *
     * @param ArrayCollection|BundleChoiceInterface[] $choices
     *
     * @return $this|BundleSlotInterface
     */
    public function setChoices($choices);

    /**
     * Returns the required.
     *
     * @return bool
     */
    public function isRequired();

    /**
     * Sets the required.
     *
     * @param bool $required
     *
     * @return $this|BundleSlotInterface
     */
    public function setRequired($required);

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
     * @return $this|BundleSlotInterface
     */
    public function setPosition($position);
}

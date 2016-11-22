<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface OptionGroupInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OptionGroupTranslationInterface translate($locale = null, $create = false)
 */
interface OptionGroupInterface extends RM\TranslatableInterface, RM\SortableInterface
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
     * @return $this|OptionGroupInterface
     */
    public function setProduct(ProductInterface $product = null);

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
     * @return $this|OptionGroupInterface
     */
    public function setName($name);

    /**
     * Returns the (translated) title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Returns the required.
     *
     * @return boolean
     */
    public function isRequired();

    /**
     * Sets the required.
     *
     * @param boolean $required
     *
     * @return $this|OptionGroupInterface
     */
    public function setRequired($required);

    /**
     * Returns the options.
     *
     * @return ArrayCollection|OptionInterface[]
     */
    public function getOptions();

    /**
     * Returns whether the group has the option or not.
     *
     * @param OptionInterface $option
     *
     * @return bool
     */
    public function hasOption(OptionInterface $option);

    /**
     * Adds the option.
     *
     * @param OptionInterface $option
     *
     * @return $this|OptionGroupInterface
     */
    public function addOption(OptionInterface $option);

    /**
     * Removes the option.
     *
     * @param OptionInterface $option
     *
     * @return $this|OptionGroupInterface
     */
    public function removeOption(OptionInterface $option);

    /**
     * Sets the options.
     *
     * @param ArrayCollection|OptionInterface[] $options
     *
     * @return $this|OptionGroupInterface
     * @internal
     */
    public function setOptions(ArrayCollection $options);
}

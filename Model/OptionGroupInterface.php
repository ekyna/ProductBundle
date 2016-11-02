<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface OptionGroupInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OptionGroupInterface extends ResourceInterface
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
     */
    public function setName($name);

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
     */
    public function setPosition($position);
}

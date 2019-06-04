<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface OptionInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OptionTranslationInterface translate($locale = null, $create = false)
 */
interface OptionInterface extends RM\TranslatableInterface, RM\SortableInterface, TaxableInterface
{
    /**
     * Returns the group.
     *
     * @return OptionGroupInterface
     */
    public function getGroup();

    /**
     * Sets the group.
     *
     * @param OptionGroupInterface $group
     *
     * @return $this|OptionInterface
     */
    public function setGroup(OptionGroupInterface $group = null);

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
     * @return $this|OptionInterface
     */
    public function setProduct(ProductInterface $product = null);

    /**
     * Returns whether to cascade option groups.
     *
     * @return bool
     */
    public function isCascade();

    /**
     * Sets whether to cascade option groups.
     *
     * @param bool $inherit
     *
     * @return $this|OptionInterface
     */
    public function setCascade(bool $inherit);

    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation();

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return $this|OptionInterface
     */
    public function setDesignation($designation);

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference();

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return $this|OptionInterface
     */
    public function setReference($reference);

    /**
     * Returns the (translated) title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Returns the (translated) title.
     *
     * @param string $title
     *
     * @return $this|OptionInterface
     */
    public function setTitle(string $title);

    /**
     * Returns the weight.
     *
     * @return float
     */
    public function getWeight();

    /**
     * Sets the weight.
     *
     * @param float $weight
     *
     * @return $this|OptionInterface
     */
    public function setWeight($weight);

    /**
     * Returns the net price.
     *
     * @return float|null
     */
    public function getNetPrice();

    /**
     * Sets the net price.
     *
     * @param float $netPrice
     *
     * @return $this|OptionInterface
     */
    public function setNetPrice($netPrice = null);
}

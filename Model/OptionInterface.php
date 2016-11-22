<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface OptionInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OptionTranslationInterface translate($locale = null, $create = false)
 */
interface OptionInterface extends RM\TranslatableInterface, RM\SortableInterface, RM\ResourceInterface
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
     * @return float
     */
    public function getNetPrice();

    /**
     * Sets the net price.
     *
     * @param float $netPrice
     *
     * @return $this|OptionInterface
     */
    public function setNetPrice($netPrice);

    /**
     * Returns the tax group.
     *
     * @return TaxGroupInterface
     */
    public function getTaxGroup();

    /**
     * Sets the tax group.
     *
     * @param TaxGroupInterface $group
     *
     * @return $this|OptionInterface
     */
    public function setTaxGroup(TaxGroupInterface $group);

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
     * @return $this|OptionInterface
     */
    public function setPosition($position);
}

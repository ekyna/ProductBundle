<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface BundleChoiceInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface BundleChoiceInterface extends ResourceInterface
{
    /**
     * Returns the slot.
     *
     * @return BundleSlotInterface
     */
    public function getSlot();

    /**
     * Sets the slot.
     *
     * @param BundleSlotInterface $slot
     *
     * @return $this|BundleChoiceInterface
     */
    public function setSlot(BundleSlotInterface $slot = null);

    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * Sets the product.
     *
     * @param ProductInterface $choice
     *
     * @return $this|BundleChoiceInterface
     */
    public function setProduct(ProductInterface $choice);

    /**
     * Returns the minimum quantity.
     *
     * @return int
     */
    public function getMinQuantity();

    /**
     * Sets the minimum quantity.
     *
     * @param int $quantity
     *
     * @return $this|BundleChoiceInterface
     */
    public function setMinQuantity($quantity);

    /**
     * Returns the maximum quantity.
     *
     * @return int
     */
    public function getMaxQuantity();

    /**
     * Sets the maximum quantity.
     *
     * @param int $quantity
     *
     * @return $this|BundleChoiceInterface
     */
    public function setMaxQuantity($quantity);

    /**
     * Returns the userOption.
     *
     * @return boolean
     * @todo
     */
    public function isUserOption();

    /**
     * Sets the user option.
     *
     * @param boolean $userOption
     *
     * @return $this|BundleChoiceInterface
     * @todo
     */
    public function setUserOption($userOption);

    /**
     * Returns the rules.
     *
     * @return ArrayCollection|BundleChoiceRuleInterface[]
     */
    public function getRules();

    /**
     * Returns whether the bundle choice has the rule or not.
     *
     * @param BundleChoiceRuleInterface $rule
     *
     * @return bool
     */
    public function hasRule(BundleChoiceRuleInterface $rule);

    /**
     * Adds the rule.
     *
     * @param BundleChoiceRuleInterface $rule
     *
     * @return $this|BundleChoiceInterface
     */
    public function addRule(BundleChoiceRuleInterface $rule);

    /**
     * Removes the rule.
     *
     * @param BundleChoiceRuleInterface $rule
     *
     * @return $this|BundleChoiceInterface
     */
    public function removeRule(BundleChoiceRuleInterface $rule);

    /**
     * Sets the rules.
     *
     * @param ArrayCollection|BundleChoiceRuleInterface[] $rules
     *
     * @return $this|BundleChoiceInterface
     * @internal
     */
    public function setRules(ArrayCollection $rules);

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
     * @return $this|BundleChoiceInterface
     */
    public function setPosition($position);
}

<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface PricingRuleInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PricingRuleInterface extends ResourceInterface
{
    /**
     * Returns the pricing.
     *
     * @return PricingInterface
     */
    public function getPricing();

    /**
     * Sets the pricing.
     *
     * @param PricingInterface $pricing
     *
     * @return $this|PricingRuleInterface
     */
    public function setPricing(PricingInterface $pricing = null);

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
     * @return $this|PricingRuleInterface
     */
    public function setMinQuantity($quantity);

    /**
     * Returns the percent.
     *
     * @return float
     */
    public function getPercent();

    /**
     * Sets the percent.
     *
     * @param float $percent
     *
     * @return $this|PricingRuleInterface
     */
    public function setPercent($percent);
}

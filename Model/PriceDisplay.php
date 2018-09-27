<?php

namespace Ekyna\Bundle\ProductBundle\Model;

/**
 * Class PriceDisplay
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceDisplay
{
    /**
     * @var float
     */
    private $amount;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $originalPrice;

    /**
     * @var string
     */
    private $finalPrice;

    /**
     * @var string
     */
    private $specialPercent;

    /**
     * @var string
     */
    private $pricingPercent;


    /**
     * Constructor.
     *
     * @param float $amount
     * @param string $from
     * @param string $originalPrice
     * @param string $finalPrice
     */
    public function __construct(float $amount, string $from, string $originalPrice, string $finalPrice)
    {
        $this->amount = $amount;
        $this->from = $from;
        $this->originalPrice = $originalPrice;
        $this->finalPrice = $finalPrice;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->from . $this->originalPrice . $this->finalPrice;
    }

    /**
     * Returns the amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Returns the from.
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Returns the original price.
     *
     * @return string
     */
    public function getOriginalPrice()
    {
        return $this->originalPrice;
    }

    /**
     * Returns the final price.
     *
     * @return string
     */
    public function getFinalPrice()
    {
        return $this->finalPrice;
    }

    /**
     * Returns the special percent.
     *
     * @return string
     */
    public function getSpecialPercent()
    {
        return $this->specialPercent;
    }

    /**
     * Sets the special percentage.
     *
     * @param string $percent
     *
     * @return PriceDisplay
     */
    public function setSpecialPercent($percent)
    {
        $this->specialPercent = $percent;

        return $this;
    }

    /**
     * Returns the pricing percentage.
     *
     * @return string
     */
    public function getPricingPercent()
    {
        return $this->pricingPercent;
    }

    /**
     * Sets the pricing percentage.
     *
     * @param string $percent
     *
     * @return PriceDisplay
     */
    public function setPricingPercent($percent)
    {
        $this->pricingPercent = $percent;

        return $this;
    }
}

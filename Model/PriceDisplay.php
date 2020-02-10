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
    private $endsAt;

    /**
     * @var string
     */
    private $specialPercent;

    /**
     * @var string
     */
    private $pricingPercent;

    /**
     * @var string
     */
    private $specialLabel;

    /**
     * @var string
     */
    private $pricingLabel;

    /**
     * @var array
     */
    private $mentions;


    /**
     * Constructor.
     *
     * @param float $amount
     * @param string $from
     * @param string $originalPrice
     * @param string $finalPrice
     * @param string $endsAt
     */
    public function __construct(float $amount, string $from, string $originalPrice, string $finalPrice, string $endsAt)
    {
        $this->amount = $amount;
        $this->from = $from;
        $this->originalPrice = $originalPrice;
        $this->finalPrice = $finalPrice;
        $this->endsAt = $endsAt;
        $this->specialPercent = '';
        $this->specialLabel = '';
        $this->pricingPercent = '';
        $this->pricingLabel = '';
        $this->mentions = [];
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
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Returns the from.
     *
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * Returns the original price.
     *
     * @return string
     */
    public function getOriginalPrice(): string
    {
        return $this->originalPrice;
    }

    /**
     * Returns the final price.
     *
     * @return string
     */
    public function getFinalPrice(): string
    {
        return $this->finalPrice;
    }

    /**
     * Returns the endsAt.
     *
     * @return string
     */
    public function getEndsAt(): string
    {
        return $this->endsAt;
    }

    /**
     * Returns the special offer percentage.
     *
     * @return string
     */
    public function getSpecialPercent(): string
    {
        return $this->specialPercent;
    }

    /**
     * Sets the special offer percentage.
     *
     * @param string $percent
     *
     * @return PriceDisplay
     */
    public function setSpecialPercent(string $percent): self
    {
        $this->specialPercent = $percent;

        return $this;
    }

    /**
     * Returns the special offer Label.
     *
     * @return string
     */
    public function getSpecialLabel(): string
    {
        return $this->specialLabel;
    }

    /**
     * Sets the special offer label.
     *
     * @param string $label
     *
     * @return PriceDisplay
     */
    public function setSpecialLabel(string $label): self
    {
        $this->specialLabel = $label;

        return $this;
    }

    /**
     * Returns the pricing offer percentage.
     *
     * @return string
     */
    public function getPricingPercent(): string
    {
        return $this->pricingPercent;
    }

    /**
     * Sets the pricing offer percentage.
     *
     * @param string $percent
     *
     * @return PriceDisplay
     */
    public function setPricingPercent(string $percent): self
    {
        $this->pricingPercent = $percent;

        return $this;
    }

    /**
     * Returns the pricing offer label.
     *
     * @return string
     */
    public function getPricingLabel(): string
    {
        return $this->pricingLabel;
    }

    /**
     * Sets the pricing offer label.
     *
     * @param string $label
     *
     * @return PriceDisplay
     */
    public function setPricingLabel(string $label): self
    {
        $this->pricingLabel = $label;

        return $this;
    }

    /**
     * Adds the mentions.
     *
     * @param string $mention
     *
     * @return $this
     */
    public function addMention(string $mention): self
    {
        $this->mentions[] = $mention;

        return $this;
    }

    /**
     * Returns the mentions.
     *
     * @return string[]
     */
    public function getMentions(): array
    {
        return $this->mentions;
    }
}

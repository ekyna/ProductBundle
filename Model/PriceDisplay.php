<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Decimal\Decimal;

/**
 * Class PriceDisplay
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PriceDisplay
{
    private Decimal $amount;
    private string  $from;
    private string  $originalPrice;
    private string  $finalPrice;
    private string  $endsAt;
    private string  $specialPercent = '';
    private string  $pricingPercent = '';
    private string  $specialLabel   = '';
    private string  $pricingLabel   = '';
    /** @var array<string> */
    private array $mentions = [];

    public function __construct(
        Decimal $amount,
        string  $from,
        string  $originalPrice,
        string  $finalPrice,
        string  $endsAt
    ) {
        $this->amount = $amount;
        $this->from = $from;
        $this->originalPrice = $originalPrice;
        $this->finalPrice = $finalPrice;
        $this->endsAt = $endsAt;
    }

    public function __toString(): string
    {
        return $this->from . $this->originalPrice . $this->finalPrice;
    }

    public function getAmount(): Decimal
    {
        return $this->amount;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getOriginalPrice(): string
    {
        return $this->originalPrice;
    }

    public function getFinalPrice(): string
    {
        return $this->finalPrice;
    }

    public function getEndsAt(): string
    {
        return $this->endsAt;
    }

    /**
     * Returns the special offer percentage.
     */
    public function getSpecialPercent(): string
    {
        return $this->specialPercent;
    }

    /**
     * Sets the special offer percentage.
     */
    public function setSpecialPercent(string $percent): self
    {
        $this->specialPercent = $percent;

        return $this;
    }

    /**
     * Returns the special offer Label.
     */
    public function getSpecialLabel(): string
    {
        return $this->specialLabel;
    }

    /**
     * Sets the special offer label.
     */
    public function setSpecialLabel(string $label): self
    {
        $this->specialLabel = $label;

        return $this;
    }

    /**
     * Returns the pricing offer percentage.
     */
    public function getPricingPercent(): string
    {
        return $this->pricingPercent;
    }

    /**
     * Sets the pricing offer percentage.
     */
    public function setPricingPercent(string $percent): self
    {
        $this->pricingPercent = $percent;

        return $this;
    }

    /**
     * Returns the pricing offer label.
     */
    public function getPricingLabel(): string
    {
        return $this->pricingLabel;
    }

    /**
     * Sets the pricing offer label.
     */
    public function setPricingLabel(string $label): self
    {
        $this->pricingLabel = $label;

        return $this;
    }

    public function addMention(string $mention): self
    {
        $this->mentions[] = $mention;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getMentions(): array
    {
        return $this->mentions;
    }
}

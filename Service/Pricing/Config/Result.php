<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing\Config;

use Ekyna\Bundle\ProductBundle\Entity\Offer;

/**
 * Class Result
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Result
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var bool
     */
    protected $startingFrom = false;

    /**
     * @var float
     */
    protected $originalPrice;

    /**
     * @var float
     */
    protected $basePrice;

    /**
     * @var float
     */
    protected $sellPrice;

    /**
     * @var float[]
     */
    protected $discounts;


    /**
     * Constructor.
     *
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;

        $this->startingFrom = false;
        $this->originalPrice = 0;
        $this->basePrice = 0;
        $this->sellPrice = 0;

        $this->discounts = [
            Offer::TYPE_SPECIAL => 0,
            Offer::TYPE_PRICING => 0,
        ];
    }

    /**
     * Returns the key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Sets the starting from.
     *
     * @param bool $from
     *
     * @return Result
     */
    public function setStartingFrom(bool $from): self
    {
        $this->startingFrom = $from;

        return $this;
    }

    /**
     * Returns the starting from.
     *
     * @return bool
     */
    public function isStartingFrom(): bool
    {
        return $this->startingFrom;
    }

    /**
     * Adds the original price.
     *
     * @param float $amount
     *
     * @return $this
     */
    public function addOriginalPrice(float $amount): self
    {
        $this->originalPrice += $amount;

        return $this;
    }

    /**
     * Returns the netPrice.
     *
     * @return float
     */
    public function getOriginalPrice(): float
    {
        return $this->originalPrice;
    }

    /**
     * Adds the base price.
     *
     * @param float $amount
     *
     * @return $this
     */
    public function addBasePrice(float $amount): self
    {
        $this->basePrice += $amount;

        return $this;
    }

    /**
     * Returns the base price.
     *
     * @return float
     */
    public function getBasePrice(): float
    {
        return $this->basePrice;
    }

    /**
     * Adds the sell price.
     *
     * @param float $amount
     *
     * @return $this
     */
    public function addSellPrice(float $amount): self
    {
        $this->sellPrice += $amount;

        return $this;
    }

    /**
     * Returns the sell price.
     *
     * @return float
     */
    public function getSellPrice(): float
    {
        return $this->sellPrice;
    }

    /**
     * Adds the discount amount for the given type.
     *
     * @param string $type
     * @param float  $amount
     *
     * @return $this
     */
    public function addDiscount(string $type, float $amount): self
    {
        $this->discounts[$type] += $amount;

        return $this;
    }

    /**
     * Returns the discount amount for the given type.
     *
     * @param string $type
     *
     * @return float
     */
    public function getDiscount(string $type): float
    {
        return $this->discounts[$type];
    }

    /**
     * Returns the array result.
     *
     * @return array|null
     */
    public function toArray(): ?array
    {
        // Build price for this group/country couple
        [$group, $country] = explode('-', $this->key);
        if (0 === $group = intval($group)) {
            $group = null;
        }
        if (0 === $country = intval($country)) {
            $country = null;
        }

        $details = [];
        $base = $this->originalPrice;
        foreach ([Offer::TYPE_SPECIAL, Offer::TYPE_PRICING] as $type) {
            if (empty($this->discounts[$type])) {
                continue;
            }

            $discount = $this->discounts[$type];
            $details[$type] = round($discount * 100 / $base, 5);
            $base -= $discount;
        }

        $percent = 0;
        if (0 < $this->originalPrice) {
            $percent = round(($this->originalPrice - $this->sellPrice) * 100 / $this->originalPrice, 2);
        }

        if (0 < $percent) {
            return [
                'details'        => $details,
                'starting_from'  => $this->startingFrom,
                'original_price' => $this->originalPrice,
                'sell_price'     => $this->sellPrice,
                'percent'        => $percent,
                'group_id'       => $group,
                'country_id'     => $country,
            ];
        }

        return null;
    }
}

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
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Adds the original price.
     *
     * @param float $amount
     *
     * @return $this
     */
    public function addOriginalPrice(float $amount)
    {
        $this->originalPrice += $amount;

        return $this;
    }

    /**
     * Returns the netPrice.
     *
     * @return float
     */
    public function getOriginalPrice()
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
    public function addBasePrice(float $amount)
    {
        $this->basePrice += $amount;

        return $this;
    }

    /**
     * Returns the netPrice.
     *
     * @return float
     */
    public function getBasePrice()
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
    public function addSellPrice(float $amount)
    {
        $this->sellPrice += $amount;

        return $this;
    }

    /**
     * Returns the netPrice.
     *
     * @return float
     */
    public function getSellPrice()
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
    public function addDiscount(string $type, float $amount)
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
    public function getDiscount(string $type)
    {
        return $this->discounts[$type];
    }

    /**
     * Returns the array result.
     *
     * @return array
     */
    public function toArray()
    {
        // Build price for this group/country couple
        list($group, $country) = explode('-', $this->key);
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

        return [
            'details'        => $details,
            'starting_from'  => true,
            'original_price' => $this->originalPrice,
            'sell_price'     => $this->sellPrice,
            'percent'        => $percent,
            'group_id'       => $group,
            'country_id'     => $country,
        ];
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\Service\Pricing\Config;

/**
 * Class Item
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing\Config
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Item
{
    /**
     * @var float
     */
    protected $netPrice;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var bool
     */
    protected $visible = true;

    /**
     * @var array
     */
    protected $offers = [];


    /**
     * Constructor.
     *
     * @param float $netPrice
     * @param float $quantity
     */
    public function __construct(float $netPrice = 0.0, float $quantity = 1.0)
    {
        $this->netPrice = $netPrice;
        $this->quantity = $quantity;
    }

    /**
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * Sets the net price.
     *
     * @param float $price
     *
     * @return $this
     */
    public function setNetPrice(float $price)
    {
        $this->netPrice = $price;

        return $this;
    }

    /**
     * Adds the net price.
     *
     * @param float $price
     *
     * @return $this
     */
    public function addNetPrice(float $price)
    {
        $this->netPrice += $price;

        return $this;
    }

    /**
     * Returns the quantity.
     *
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Sets the quantity.
     *
     * @param float $quantity
     *
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Returns the visible.
     *
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Sets the visible.
     *
     * @param bool $visible
     *
     * @return $this
     */
    public function setVisible(bool $visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Returns the offers.
     *
     * @return array
     */
    public function getOffers()
    {
        return $this->offers;
    }

    /**
     * Returns the offer corresponding to the given key.
     *
     * @param string $key
     *
     * @return array|null
     */
    public function getOffer(string $key)
    {
        list($group, $country) = explode('-', $key);

        $keys = array_unique([$key, '0-' . $country, $group . '-0', '0-0']);

        foreach($keys as $k) {
            if (isset($this->offers[$k])) {
                return $this->offers[$k];
            }
        }

        return null;
    }

    /**
     * Sets the offers.
     *
     * @param array $offers
     *
     * @return $this
     */
    public function setOffers(array $offers)
    {
        $this->offers = $offers;

        return $this;
    }
}

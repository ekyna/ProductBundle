<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class Offer
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Offer implements ResourceInterface
{
    const TYPE_SPECIAL = 'special';
    const TYPE_PRICING = 'pricing';

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $minQuantity;

    /**
     * @var float
     */
    private $percent;

    /**
     * @var float
     */
    private $netPrice;

    /**
     * @var array
     */
    private $details = [];

    /**
     * @var Model\ProductInterface
     */
    private $product;

    /**
     * @var CustomerGroupInterface
     */
    private $group;

    /**
     * @var CountryInterface
     */
    private $country;

    /**
     * @var Model\SpecialOfferInterface
     */
    private $specialOffer;

    /**
     * @var Model\PricingInterface
     */
    private $pricing;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->details = [];
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the minimum quantity.
     *
     * @return float
     */
    public function getMinQuantity()
    {
        return $this->minQuantity;
    }

    /**
     * Sets the minimum quantity.
     *
     * @param float $quantity
     *
     * @return Offer
     */
    public function setMinQuantity(float $quantity)
    {
        $this->minQuantity = $quantity;

        return $this;
    }

    /**
     * Returns the percent.
     *
     * @return int
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * Sets the percent.
     *
     * @param float $percent
     *
     * @return Offer
     */
    public function setPercent(float $percent)
    {
        $this->percent = $percent;

        return $this;
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
     * @param float $amount
     *
     * @return Offer
     */
    public function setNetPrice($amount)
    {
        $this->netPrice = $amount;

        return $this;
    }

    /**
     * Returns the details.
     *
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Sets the details.
     *
     * @param array $details
     *
     * @return Offer
     */
    public function setDetails(array $details)
    {
        foreach ($details as $type => $percent) {
            $this->addDetails((string)$type, (float)$percent);
        }

        return $this;
    }

    /**
     * Adds the detail.
     *
     * @param string $type
     * @param float  $percent
     *
     * @return Offer
     */
    public function addDetails(string $type, float $percent)
    {
        if (!in_array($type, [static::TYPE_PRICING, static::TYPE_SPECIAL], true)) {
            throw new InvalidArgumentException("Unexpected offer type");
        }

        $this->details[$type] = $percent;

        return $this;
    }

    /**
     * Returns the product.
     *
     * @return Model\ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Sets the product.
     *
     * @param Model\ProductInterface $product
     *
     * @return Offer
     */
    public function setProduct(Model\ProductInterface $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Returns the group.
     *
     * @return CustomerGroupInterface
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Sets the group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return Offer
     */
    public function setGroup(CustomerGroupInterface $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Returns the country.
     *
     * @return CountryInterface
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Sets the country.
     *
     * @param CountryInterface $country
     *
     * @return Offer
     */
    public function setCountry(CountryInterface $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Returns the special offer.
     *
     * @return Model\SpecialOfferInterface
     */
    public function getSpecialOffer()
    {
        return $this->specialOffer;
    }

    /**
     * Sets the special offer.
     *
     * @param Model\SpecialOfferInterface $specialOffer
     *
     * @return Offer
     */
    public function setSpecialOffer(Model\SpecialOfferInterface $specialOffer = null)
    {
        $this->specialOffer = $specialOffer;

        return $this;
    }

    /**
     * Returns the pricing.
     *
     * @return Model\PricingInterface
     */
    public function getPricing()
    {
        return $this->pricing;
    }

    /**
     * Sets the pricing.
     *
     * @param Model\PricingInterface $pricing
     *
     * @return Offer
     */
    public function setPricing(Model\PricingInterface $pricing = null)
    {
        $this->pricing = $pricing;

        return $this;
    }

    /**
     * Returns the detailed percentages.
     *
     * @return float[]
     */
    public function getDetailedPercents()
    {
        $percents = [];

        foreach ([Offer::TYPE_SPECIAL, Offer::TYPE_PRICING] as $type) {
            if (isset($this->details[$type])) {
                $percents[] = $this->details[$type];
            }
        }

        return $percents;
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class Price
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Price implements ResourceInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var bool
     */
    private $startingFrom;

    /**
     * @var float
     */
    private $originalPrice;

    /**
     * @var float
     */
    private $sellPrice;

    /**
     * @var float
     */
    private $percent;

    /**
     * @var array
     */
    private $details;

    /**
     * @var ProductInterface
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
     * Constructor.
     */
    public function __construct()
    {
        $this->details = [];
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        $this->id = null;
        $this->product = null;
        $this->country = null;
        $this->group = null;
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
     * Returns whether this price is "starting from".
     *
     * @return bool
     */
    public function isStartingFrom()
    {
        return $this->startingFrom;
    }

    /**
     * Sets whether this price is "starting from".
     *
     * @param bool $from
     *
     * @return Price
     */
    public function setStartingFrom(bool $from)
    {
        $this->startingFrom = $from;

        return $this;
    }

    /**
     * Returns the original price.
     *
     * @return float
     */
    public function getOriginalPrice()
    {
        return $this->originalPrice;
    }

    /**
     * Sets the original price.
     *
     * @param float $price
     *
     * @return Price
     */
    public function setOriginalPrice(float $price)
    {
        $this->originalPrice = $price;

        return $this;
    }

    /**
     * Returns the sell price.
     *
     * @return float
     */
    public function getSellPrice()
    {
        return $this->sellPrice;
    }

    /**
     * Sets the sell price.
     *
     * @param float $price
     *
     * @return Price
     */
    public function setSellPrice(float $price)
    {
        $this->sellPrice = $price;

        return $this;
    }

    /**
     * Returns the percent.
     *
     * @return float
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
     * @return Price
     */
    public function setPercent(float $percent)
    {
        $this->percent = $percent;

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
     * @return Price
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
     * @return Price
     */
    public function addDetails(string $type, float $percent)
    {
        if (!in_array($type, [Offer::TYPE_PRICING, Offer::TYPE_SPECIAL], true)) {
            throw new InvalidArgumentException("Unexpected offer type");
        }

        $this->details[$type] = $percent;

        return $this;
    }

    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Sets the product.
     *
     * @param ProductInterface $product
     *
     * @return Price
     */
    public function setProduct(ProductInterface $product)
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
     * @return Price
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
     * @return Price
     */
    public function setCountry(CountryInterface $country = null)
    {
        $this->country = $country;

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

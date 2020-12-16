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
     * @var \DateTime
     */
    private $endsAt;

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
        $this->startingFrom = false;
        $this->originalPrice = 0;
        $this->sellPrice = 0;
        $this->percent = 0;
        $this->details = [];
    }

    /**
     * Clones the price.
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
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Returns whether this price is "starting from".
     *
     * @return bool
     */
    public function isStartingFrom(): bool
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
    public function setStartingFrom(bool $from): self
    {
        $this->startingFrom = $from;

        return $this;
    }

    /**
     * Returns the original price.
     *
     * @return float
     */
    public function getOriginalPrice(): float
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
    public function setOriginalPrice(float $price): self
    {
        $this->originalPrice = $price;

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
     * Sets the sell price.
     *
     * @param float $price
     *
     * @return Price
     */
    public function setSellPrice(float $price): self
    {
        $this->sellPrice = $price;

        return $this;
    }

    /**
     * Returns the percent.
     *
     * @return float
     */
    public function getPercent(): float
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
    public function setPercent(float $percent): self
    {
        $this->percent = $percent;

        return $this;
    }

    /**
     * Returns the details.
     *
     * @return array
     */
    public function getDetails(): array
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
    public function setDetails(array $details): self
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
    public function addDetails(string $type, float $percent): self
    {
        if (!in_array($type, [Offer::TYPE_PRICING, Offer::TYPE_SPECIAL], true)) {
            throw new InvalidArgumentException("Unexpected offer type");
        }

        $this->details[$type] = $percent;

        return $this;
    }

    /**
     * Returns the "ends at" date.
     *
     * @return \DateTime
     */
    public function getEndsAt(): ?\DateTime
    {
        return $this->endsAt;
    }

    /**
     * Sets the "ends at" date.
     *
     * @param \DateTime $endsAt
     *
     * @return Price
     */
    public function setEndsAt(\DateTime $endsAt = null): self
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface
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
    public function setProduct(ProductInterface $product): self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Returns the group.
     *
     * @return CustomerGroupInterface
     */
    public function getGroup(): ?CustomerGroupInterface
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
    public function setGroup(CustomerGroupInterface $group = null): self
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Returns the country.
     *
     * @return CountryInterface
     */
    public function getCountry(): ?CountryInterface
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
    public function setCountry(CountryInterface $country = null): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Returns the detailed percentages.
     *
     * @return float[]
     */
    public function getDetailedPercents(): array
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

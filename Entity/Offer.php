<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class Price
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
     * @var float
     */
    private $netPrice;

    /**
     * @var int
     */
    private $percent;

    /**
     * @var int
     */
    private $minQuantity;

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
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @param int $percent
     *
     * @return Offer
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;

        return $this;
    }

    /**
     * Returns the minimum quantity.
     *
     * @return int
     */
    public function getMinQuantity()
    {
        return $this->minQuantity;
    }

    /**
     * Sets the minimum quantity.
     *
     * @param int $quantity
     *
     * @return Offer
     */
    public function setMinQuantity($quantity)
    {
        $this->minQuantity = $quantity;

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
     * Returns this offer's cache id.
     *
     * @param null $quantity
     * @param bool $multiple
     *
     * @return string
     */
    public function getCacheId($quantity = null, $multiple = true)
    {
        return static::buildCacheId(
            $this->product,
            $this->group,
            $this->country,
            $quantity,
            $multiple
        );
    }

    /**
     * Builds and returns the offer(s) cache id.
     *
     * @param Model\ProductInterface      $product
     * @param CustomerGroupInterface|null $group
     * @param CountryInterface|null       $country
     * @param float                       $quantity
     * @param bool                        $multiple
     *
     * @return string
     *
     * @see \Ekyna\Bundle\ProductBundle\Repository\OfferRepository
     */
    public static function buildCacheId(
        Model\ProductInterface $product,
        CustomerGroupInterface $group = null,
        CountryInterface $country = null,
        $quantity = null,
        $multiple = true
    ) {
        return static::buildCacheIdByIds(
            $product->getId(),
            $group ? $group->getId() : 0,
            $country ? $country->getId() : 0,
            $multiple,
            $quantity
        );
    }

    /**
     * Builds and returns the offer(s) cache id.
     *
     * @param int   $productId
     * @param int   $groupId
     * @param int   $countryId
     * @param float $quantity
     * @param bool  $multiple
     *
     * @return string
     *
     * @see \Ekyna\Bundle\ProductBundle\EventListener\OfferEventSubscriber
     */
    public static function buildCacheIdByIds(
        $productId,
        $groupId,
        $countryId,
        $quantity = null,
        $multiple = true
    ) {
        $id = sprintf(
            'product_offer%s_%d_%d_%d',
            $multiple ? 's' : '',
            intval($productId),
            intval($groupId),
            intval($countryId)
        );

        if ($quantity) {
            $id .= '_' . intval($quantity);
        }

        return $id;
    }
}

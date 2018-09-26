<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Model\TaggedEntityInterface;
use Ekyna\Component\Resource\Model\TrackAssociationInterface;

/**
 * Interface SpecialOfferInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SpecialOfferInterface extends TaggedEntityInterface, TrackAssociationInterface
{
    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Returns the percent.
     *
     * @return int
     */
    public function getPercent();

    /**
     * Sets the percent.
     *
     * @param int $percent
     *
     * @return $this|SpecialOfferInterface
     */
    public function setPercent($percent);

    /**
     * Returns the minimum quantity.
     *
     * @return int
     */
    public function getMinQuantity();

    /**
     * Sets the minimum quantity.
     *
     * @param int $quantity
     *
     * @return $this|SpecialOfferInterface
     */
    public function setMinQuantity($quantity);

    /**
     * Returns the "starts at" date (including).
     *
     * @return \DateTime
     */
    public function getStartsAt();

    /**
     * Sets the "starts at" date (including).
     *
     * @param \DateTime $date
     *
     * @return $this|SpecialOfferInterface
     */
    public function setStartsAt(\DateTime $date = null);

    /**
     * Returns the "ends at" date (including).
     *
     * @return \DateTime
     */
    public function getEndsAt();

    /**
     * Sets the "ends at" date (including).
     *
     * @param \DateTime $date
     *
     * @return $this|SpecialOfferInterface
     */
    public function setEndsAt(\DateTime $date = null);

    /**
     * Returns whether this special offer stacks over pricing rules.
     *
     * @return bool
     */
    public function isStack();

    /**
     * Sets whether this special offer stacks over pricing rules.
     *
     * @param bool $stack
     *
     * @return $this|SpecialOfferInterface
     */
    public function setStack(bool $stack);

    /**
     * Returns the enabled.
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Sets the enabled.
     *
     * @param bool $enabled
     *
     * @return $this|SpecialOfferInterface
     */
    public function setEnabled(bool $enabled);

    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * Sets the product.
     *
     * @param ProductInterface $product
     */
    public function setProduct(ProductInterface $product = null);

    /**
     * Returns the products.
     *
     * @return ArrayCollection|ProductInterface[]
     */
    public function getProducts();

    /**
     * Adds the product.
     *
     * @param ProductInterface $product
     *
     * @return $this|SpecialOfferInterface
     */
    public function addProduct(ProductInterface $product);

    /**
     * Removes the product.
     *
     * @param ProductInterface $product
     *
     * @return $this|SpecialOfferInterface
     */
    public function removeProduct(ProductInterface $product);

    /**
     * Returns the brands.
     *
     * @return ArrayCollection|BrandInterface[]
     */
    public function getBrands();

    /**
     * Adds the brand.
     *
     * @param BrandInterface $brand
     *
     * @return $this|SpecialOfferInterface
     */
    public function addBrand(BrandInterface $brand);

    /**
     * Removes the brand.
     *
     * @param BrandInterface $brand
     *
     * @return $this|SpecialOfferInterface
     */
    public function removeBrand(BrandInterface $brand);

    /**
     * Returns the customerGroups.
     *
     * @return ArrayCollection|CustomerGroupInterface[]
     */
    public function getGroups();

    /**
     * Adds the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return $this|SpecialOfferInterface
     */
    public function addGroup(CustomerGroupInterface $group);

    /**
     * Removes the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return $this|SpecialOfferInterface
     */
    public function removeGroup(CustomerGroupInterface $group);

    /**
     * Returns the customerCountries.
     *
     * @return ArrayCollection|CountryInterface[]
     */
    public function getCountries();

    /**
     * Adds the customer country.
     *
     * @param CountryInterface $country
     *
     * @return $this|SpecialOfferInterface
     */
    public function addCountry(CountryInterface $country);

    /**
     * Removes the customer country.
     *
     * @param CountryInterface $country
     *
     * @return $this|SpecialOfferInterface
     */
    public function removeCountry(CountryInterface $country);
}

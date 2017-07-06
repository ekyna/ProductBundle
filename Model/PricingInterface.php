<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Model\TaggedEntityInterface;

/**
 * Interface PricingInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PricingInterface extends TaggedEntityInterface
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
     *
     * @return $this|PricingInterface
     */
    public function setName($name);

    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation();

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return $this|PricingInterface
     */
    public function setDesignation($designation);

    /**
     * Returns the customer groups.
     *
     * @return Collection|CustomerGroupInterface[]
     */
    public function getGroups();

    /**
     * Returns whether the pricing has the given customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return boolean
     */
    public function hasGroup(CustomerGroupInterface $group);

    /**
     * Adds the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return $this|PricingInterface
     */
    public function addGroup(CustomerGroupInterface $group);

    /**
     * Removes the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return $this|PricingInterface
     */
    public function removeGroup(CustomerGroupInterface $group);

    /**
     * Returns the countries.
     *
     * @return Collection|CountryInterface[]
     */
    public function getCountries();

    /**
     * Returns whether the pricing has the given country.
     *
     * @param CountryInterface $country
     *
     * @return boolean
     */
    public function hasCountry(CountryInterface $country);

    /**
     * Adds the country.
     *
     * @param CountryInterface $country
     *
     * @return $this|PricingInterface
     */
    public function addCountry(CountryInterface $country);

    /**
     * Removes the country.
     *
     * @param CountryInterface $country
     *
     * @return $this|PricingInterface
     */
    public function removeCountry(CountryInterface $country);

    /**
     * Returns the brands.
     *
     * @return Collection|BrandInterface[]
     */
    public function getBrands();

    /**
     * Returns whether the pricing has the given brand.
     *
     * @param BrandInterface $brand
     *
     * @return boolean
     */
    public function hasBrand(BrandInterface $brand);

    /**
     * Adds the brand.
     *
     * @param BrandInterface $brand
     *
     * @return $this|PricingInterface
     */
    public function addBrand(BrandInterface $brand);

    /**
     * Removes the brand.
     *
     * @param BrandInterface $brand
     *
     * @return $this|PricingInterface
     */
    public function removeBrand(BrandInterface $brand);

    /**
     * Returns the rules.
     *
     * @return Collection|PricingRuleInterface[]
     */
    public function getRules();

    /**
     * Returns whether the pricing has the given rule.
     *
     * @param PricingRuleInterface $rule
     *
     * @return boolean
     */
    public function hasRule(PricingRuleInterface $rule);

    /**
     * Adds the rule.
     *
     * @param PricingRuleInterface $rule
     *
     * @return $this|PricingInterface
     */
    public function addRule(PricingRuleInterface $rule);

    /**
     * Removes the rule.
     *
     * @param PricingRuleInterface $rule
     *
     * @return $this|PricingInterface
     */
    public function removeRule(PricingRuleInterface $rule);
}

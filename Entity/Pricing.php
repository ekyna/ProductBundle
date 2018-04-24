<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Bundle\ProductBundle\Model\PricingInterface;
use Ekyna\Bundle\ProductBundle\Model\PricingRuleInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Model\TaggedEntityTrait;

/**
 * Class Pricing
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Pricing implements PricingInterface
{
    use TaggedEntityTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $designation;

    /**
     * @var CustomerGroupInterface[]
     */
    protected $groups;

    /**
     * @var CountryInterface[]
     */
    protected $countries;

    /**
     * @var BrandInterface[]
     */
    protected $brands;

    /**
     * @var PricingRuleInterface[]
     */
    protected $rules;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->countries = new ArrayCollection();
        $this->brands = new ArrayCollection();
        $this->rules = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * @inheritdoc
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @inheritdoc
     */
    public function hasGroup(CustomerGroupInterface $group)
    {
        return $this->groups->contains($group);
    }

    /**
     * @inheritdoc
     */
    public function addGroup(CustomerGroupInterface $group)
    {
        if (!$this->hasGroup($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeGroup(CustomerGroupInterface $group)
    {
        if ($this->hasGroup($group)) {
            $this->groups->removeElement($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * @inheritdoc
     */
    public function hasCountry(CountryInterface $country)
    {
        return $this->countries->contains($country);
    }

    /**
     * @inheritdoc
     */
    public function addCountry(CountryInterface $country)
    {
        if (!$this->hasCountry($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCountry(CountryInterface $country)
    {
        if ($this->hasCountry($country)) {
            $this->countries->removeElement($country);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBrands()
    {
        return $this->brands;
    }

    /**
     * @inheritdoc
     */
    public function hasBrand(BrandInterface $brand)
    {
        return $this->brands->contains($brand);
    }

    /**
     * @inheritdoc
     */
    public function addBrand(BrandInterface $brand)
    {
        if (!$this->hasBrand($brand)) {
            $this->brands->add($brand);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeBrand(BrandInterface $brand)
    {
        if ($this->hasBrand($brand)) {
            $this->brands->removeElement($brand);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @inheritdoc
     */
    public function hasRule(PricingRuleInterface $rule)
    {
        return $this->rules->contains($rule);
    }

    /**
     * @inheritdoc
     */
    public function addRule(PricingRuleInterface $rule)
    {
        if (!$this->hasRule($rule)) {
            $this->rules->add($rule);
            $rule->setPricing($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeRule(PricingRuleInterface $rule)
    {
        if ($this->hasRule($rule)) {
            $this->rules->removeElement($rule);
            $rule->setPricing(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getEntityTagPrefix()
    {
        return 'ekyna_product.pricing';
    }
}

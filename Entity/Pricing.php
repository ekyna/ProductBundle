<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Model\TaggedEntityTrait;
use Ekyna\Component\Resource\Model\TrackAssociationTrait;

/**
 * Class Pricing
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Pricing implements Model\PricingInterface
{
    const REL_GROUPS    = 'groups';
    const REL_COUNTRIES = 'countries';
    const REL_BRANDS    = 'brands';

    use TaggedEntityTrait,
        TrackAssociationTrait;

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
     * @var Model\ProductInterface
     */
    protected $product;

    /**
     * @var ArrayCollection|CustomerGroupInterface[]
     */
    protected $groups;

    /**
     * @var ArrayCollection|CountryInterface[]
     */
    protected $countries;

    /**
     * @var ArrayCollection|Model\BrandInterface[]
     */
    protected $brands;

    /**
     * @var ArrayCollection|Model\PricingRuleInterface[]
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
    public function __toString(): string
    {
        return $this->name ?: 'New pricing';
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
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
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @inheritdoc
     */
    public function setProduct(Model\ProductInterface $product = null)
    {
        $this->product = $product;

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
    public function hasBrand(Model\BrandInterface $brand)
    {
        return $this->brands->contains($brand);
    }

    /**
     * @inheritdoc
     */
    public function addBrand(Model\BrandInterface $brand)
    {
        if (!$this->hasBrand($brand)) {
            $this->brands->add($brand);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeBrand(Model\BrandInterface $brand)
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
    public function hasRule(Model\PricingRuleInterface $rule)
    {
        return $this->rules->contains($rule);
    }

    /**
     * @inheritdoc
     */
    public function addRule(Model\PricingRuleInterface $rule)
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
    public function removeRule(Model\PricingRuleInterface $rule)
    {
        if ($this->hasRule($rule)) {
            $this->rules->removeElement($rule);
            $rule->setPricing(null);
        }

        return $this;
    }

    /**
     * Post load lifecycle event handler.
     */
    public function onPostLoad()
    {
        $this->takeSnapshot();
    }

    /**
     * @inheritDoc
     */
    public static function getAssociationsProperties()
    {
        return [
            static::REL_GROUPS,
            static::REL_COUNTRIES,
            static::REL_BRANDS,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getEntityTagPrefix()
    {
        return 'ekyna_product.pricing';
    }
}

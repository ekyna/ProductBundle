<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Copier\CopierInterface;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\TaggedEntityTrait;
use Ekyna\Component\Resource\Model\TrackAssociationTrait;

/**
 * Class Pricing
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Pricing extends AbstractResource implements Model\PricingInterface
{
    public const REL_CUSTOMER_GROUPS = 'customerGroups';
    public const REL_PRICING_GROUPS  = 'pricingGroups';
    public const REL_COUNTRIES       = 'countries';
    public const REL_BRANDS          = 'brands';

    use TaggedEntityTrait;
    use TrackAssociationTrait;

    protected ?string                 $name        = null;
    protected ?string                 $designation = null;
    protected ?Model\ProductInterface $product     = null;
    /** @var Collection<int, CustomerGroupInterface> */
    protected Collection $customerGroups;
    /** @var Collection<int, Model\PricingGroupInterface> */
    protected Collection $pricingGroups;
    /** @var Collection<int, CountryInterface> */
    protected Collection $countries;
    /** @var Collection<int, Model\BrandInterface> */
    protected Collection $brands;
    /** @var Collection<int, Model\PricingRuleInterface> */
    protected Collection $rules;

    public function __construct()
    {
        $this->customerGroups = new ArrayCollection();
        $this->pricingGroups = new ArrayCollection();
        $this->countries = new ArrayCollection();
        $this->brands = new ArrayCollection();
        $this->rules = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->designation ?: ($this->name ?: 'New pricing');
    }

    public function __clone()
    {
        parent::__clone();

        $this->product = null;
    }

    public function onCopy(CopierInterface $copier): void
    {
        $copier->copyCollection($this, 'customerGroups', false);
        $copier->copyCollection($this, 'pricingGroups', false);
        $copier->copyCollection($this, 'countries', false);
        $copier->copyCollection($this, 'brands', false);
        $copier->copyCollection($this, 'rules', true);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Model\PricingInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): Model\PricingInterface
    {
        $this->designation = $designation;

        return $this;
    }

    public function getProduct(): ?Model\ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?Model\ProductInterface $product): Model\PricingInterface
    {
        $this->product = $product;

        return $this;
    }

    public function getCustomerGroups(): Collection
    {
        return $this->customerGroups;
    }

    public function hasCustomerGroup(CustomerGroupInterface $group): bool
    {
        return $this->customerGroups->contains($group);
    }

    public function addCustomerGroup(CustomerGroupInterface $group): Model\PricingInterface
    {
        if (!$this->hasCustomerGroup($group)) {
            $this->customerGroups->add($group);
        }

        return $this;
    }

    public function removeCustomerGroup(CustomerGroupInterface $group): Model\PricingInterface
    {
        if ($this->hasCustomerGroup($group)) {
            $this->customerGroups->removeElement($group);
        }

        return $this;
    }

    public function getPricingGroups(): Collection
    {
        return $this->pricingGroups;
    }

    public function hasPricingGroup(Model\PricingGroupInterface $group): bool
    {
        return $this->pricingGroups->contains($group);
    }

    public function addPricingGroup(Model\PricingGroupInterface $group): Model\PricingInterface
    {
        if (!$this->hasPricingGroup($group)) {
            $this->pricingGroups->add($group);
        }

        return $this;
    }

    public function removePricingGroup(Model\PricingGroupInterface $group): Model\PricingInterface
    {
        if ($this->hasPricingGroup($group)) {
            $this->pricingGroups->removeElement($group);
        }

        return $this;
    }

    public function getCountries(): Collection
    {
        return $this->countries;
    }

    public function hasCountry(CountryInterface $country): bool
    {
        return $this->countries->contains($country);
    }

    public function addCountry(CountryInterface $country): Model\PricingInterface
    {
        if (!$this->hasCountry($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    public function removeCountry(CountryInterface $country): Model\PricingInterface
    {
        if ($this->hasCountry($country)) {
            $this->countries->removeElement($country);
        }

        return $this;
    }

    public function getBrands(): Collection
    {
        return $this->brands;
    }

    public function hasBrand(Model\BrandInterface $brand): bool
    {
        return $this->brands->contains($brand);
    }

    public function addBrand(Model\BrandInterface $brand): Model\PricingInterface
    {
        if (!$this->hasBrand($brand)) {
            $this->brands->add($brand);
        }

        return $this;
    }

    public function removeBrand(Model\BrandInterface $brand): Model\PricingInterface
    {
        if ($this->hasBrand($brand)) {
            $this->brands->removeElement($brand);
        }

        return $this;
    }

    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function hasRule(Model\PricingRuleInterface $rule): bool
    {
        return $this->rules->contains($rule);
    }

    public function addRule(Model\PricingRuleInterface $rule): Model\PricingInterface
    {
        if (!$this->hasRule($rule)) {
            $this->rules->add($rule);
            $rule->setPricing($this);
        }

        return $this;
    }

    public function removeRule(Model\PricingRuleInterface $rule): Model\PricingInterface
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
    public function onPostLoad(): void
    {
        $this->takeSnapshot();
    }

    public static function getAssociationsProperties(): array
    {
        return [
            static::REL_CUSTOMER_GROUPS,
            static::REL_PRICING_GROUPS,
            static::REL_COUNTRIES,
            static::REL_BRANDS,
        ];
    }

    public static function getEntityTagPrefix(): string
    {
        return 'ekyna_product.pricing';
    }
}

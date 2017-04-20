<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Model\TaggedEntityInterface;
use Ekyna\Component\Resource\Model\TrackAssociationInterface;

/**
 * Interface PricingInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PricingInterface extends TaggedEntityInterface, TrackAssociationInterface
{
    public function getName(): ?string;

    public function setName(?string $name): PricingInterface;

    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): PricingInterface;

    /**
     * @return Collection<CustomerGroupInterface>
     */
    public function getGroups(): Collection;

    public function hasGroup(CustomerGroupInterface $group): bool;

    public function addGroup(CustomerGroupInterface $group): PricingInterface;

    public function removeGroup(CustomerGroupInterface $group): PricingInterface;

    /**
     * @return Collection<CountryInterface>
     */
    public function getCountries(): Collection;

    public function hasCountry(CountryInterface $country): bool;

    public function addCountry(CountryInterface $country): PricingInterface;

    public function removeCountry(CountryInterface $country): PricingInterface;

    /**
     * @return Collection<BrandInterface>
     */
    public function getBrands(): Collection;

    public function hasBrand(BrandInterface $brand): bool;

    public function addBrand(BrandInterface $brand): PricingInterface;

    public function removeBrand(BrandInterface $brand): PricingInterface;

    public function getRules(): Collection;

    public function hasRule(PricingRuleInterface $rule): bool;

    public function addRule(PricingRuleInterface $rule): PricingInterface;

    public function removeRule(PricingRuleInterface $rule): PricingInterface;
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Copier\CopyInterface;
use Ekyna\Component\Resource\Model\TaggedEntityInterface;
use Ekyna\Component\Resource\Model\TrackAssociationInterface;

/**
 * Interface SpecialOfferInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SpecialOfferInterface extends TaggedEntityInterface, TrackAssociationInterface, CopyInterface
{
    public function getName(): ?string;

    public function setName(?string $name): SpecialOfferInterface;

    public function getDesignation(): ?string;

    public function setDesignation(?string $designation): SpecialOfferInterface;

    public function getPercent(): Decimal;

    public function setPercent(Decimal $percent): SpecialOfferInterface;

    public function getMinQuantity(): Decimal;

    public function setMinQuantity(Decimal $quantity): SpecialOfferInterface;

    /**
     * Returns the "starts at" date (including).
     */
    public function getStartsAt(): ?DateTimeInterface;

    /**
     * Sets the "starts at" date (including).
     */
    public function setStartsAt(?DateTimeInterface $date): SpecialOfferInterface;

    /**
     * Returns the "ends at" date (including).
     */
    public function getEndsAt(): ?DateTimeInterface;

    /**
     * Sets the "ends at" date (including).
     */
    public function setEndsAt(?DateTimeInterface $date): SpecialOfferInterface;

    /**
     * Returns whether this special offer stacks over pricing rules.
     */
    public function isStack(): bool;

    /**
     * Sets whether this special offer stacks over pricing rules.
     */
    public function setStack(bool $stack): SpecialOfferInterface;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): SpecialOfferInterface;

    public function getProduct(): ?ProductInterface;

    public function setProduct(?ProductInterface $product): SpecialOfferInterface;

    /**
     * @return Collection<ProductInterface>
     */
    public function getProducts(): Collection;

    public function addProduct(ProductInterface $product): SpecialOfferInterface;

    public function removeProduct(ProductInterface $product): SpecialOfferInterface;

    /**
     * @return Collection<BrandInterface>
     */
    public function getBrands(): Collection;

    public function addBrand(BrandInterface $brand): SpecialOfferInterface;

    public function removeBrand(BrandInterface $brand): SpecialOfferInterface;

    /**
     * @return Collection<PricingGroupInterface>
     */
    public function getPricingGroups(): Collection;

    public function addPricingGroup(PricingGroupInterface $group): SpecialOfferInterface;

    public function removePricingGroup(PricingGroupInterface $group): SpecialOfferInterface;

    /**
     * @return Collection<CustomerGroupInterface>
     */
    public function getCustomerGroups(): Collection;

    public function addCustomerGroup(CustomerGroupInterface $group): SpecialOfferInterface;

    public function removeCustomerGroup(CustomerGroupInterface $group): SpecialOfferInterface;

    /**
     * @return Collection<CountryInterface>
     */
    public function getCountries(): Collection;

    public function addCountry(CountryInterface $country): SpecialOfferInterface;

    public function removeCountry(CountryInterface $country): SpecialOfferInterface;
}

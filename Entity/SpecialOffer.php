<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Model\BrandInterface;
use Ekyna\Bundle\ProductBundle\Model\PricingGroupInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Copier\CopierInterface;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\TaggedEntityTrait;
use Ekyna\Component\Resource\Model\TrackAssociationTrait;

/**
 * Class SpecialOffer
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SpecialOffer extends AbstractResource implements SpecialOfferInterface
{
    public const REL_PRODUCTS        = 'products';
    public const REL_BRANDS          = 'brands';
    public const REL_PRICING_GROUPS  = 'pricingGroups';
    public const REL_CUSTOMER_GROUPS = 'customerGroups';
    public const REL_COUNTRIES       = 'countries';

    use TaggedEntityTrait;
    use TrackAssociationTrait;

    protected ?string            $name        = null;
    protected ?string            $designation = null;
    protected Decimal            $percent;
    protected Decimal            $minQuantity;
    protected ?DateTimeInterface $startsAt    = null;
    protected ?DateTimeInterface $endsAt      = null;
    protected bool               $stack       = true;
    protected bool               $enabled     = false;
    protected ?ProductInterface  $product     = null;
    /** @var Collection<int, ProductInterface> */
    protected Collection $products;
    /** @var Collection<int, BrandInterface> */
    protected Collection $brands;
    /** @var Collection<int, PricingGroupInterface> */
    protected Collection $pricingGroups;
    /** @var Collection<int, CustomerGroupInterface> */
    protected Collection $customerGroups;
    /** @var Collection<int, CountryInterface> */
    protected Collection $countries;


    public function __construct()
    {
        $this->percent = new Decimal(0);
        $this->minQuantity = new Decimal(1);

        $this->products = new ArrayCollection();
        $this->brands = new ArrayCollection();
        $this->customerGroups = new ArrayCollection();
        $this->countries = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->designation ?: ($this->name ?: 'New special offer');
    }

    public function __clone()
    {
        parent::__clone();

        $this->product = null;
        $this->percent = clone $this->percent;
        $this->minQuantity = clone $this->minQuantity;
    }

    public function onCopy(CopierInterface $copier): void
    {
        $this->snapshot = null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): SpecialOfferInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(?string $designation): SpecialOfferInterface
    {
        $this->designation = $designation;

        return $this;
    }

    public function getPercent(): Decimal
    {
        return $this->percent;
    }

    public function setPercent(Decimal $percent): SpecialOfferInterface
    {
        $this->percent = $percent;

        return $this;
    }

    public function getMinQuantity(): Decimal
    {
        return $this->minQuantity;
    }

    public function setMinQuantity(Decimal $quantity): SpecialOfferInterface
    {
        $this->minQuantity = $quantity;

        return $this;
    }

    public function getStartsAt(): ?DateTimeInterface
    {
        return $this->startsAt;
    }

    public function setStartsAt(?DateTimeInterface $date = null): SpecialOfferInterface
    {
        $this->startsAt = $date;

        return $this;
    }

    public function getEndsAt(): ?DateTimeInterface
    {
        return $this->endsAt;
    }

    public function setEndsAt(?DateTimeInterface $date): SpecialOfferInterface
    {
        $this->endsAt = $date;

        return $this;
    }

    public function isStack(): bool
    {
        return $this->stack;
    }

    public function setStack(bool $stack): SpecialOfferInterface
    {
        $this->stack = $stack;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): SpecialOfferInterface
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product): SpecialOfferInterface
    {
        $this->product = $product;

        return $this;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(ProductInterface $product): SpecialOfferInterface
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    public function removeProduct(ProductInterface $product): SpecialOfferInterface
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
        }

        return $this;
    }

    public function getBrands(): Collection
    {
        return $this->brands;
    }

    public function addBrand(BrandInterface $brand): SpecialOfferInterface
    {
        if (!$this->brands->contains($brand)) {
            $this->brands->add($brand);
        }

        return $this;
    }

    public function removeBrand(BrandInterface $brand): SpecialOfferInterface
    {
        if ($this->brands->contains($brand)) {
            $this->brands->removeElement($brand);
        }

        return $this;
    }

    public function getPricingGroups(): Collection
    {
        return $this->pricingGroups;
    }

    public function addPricingGroup(PricingGroupInterface $group): SpecialOfferInterface
    {
        if (!$this->pricingGroups->contains($group)) {
            $this->pricingGroups->add($group);
        }

        return $this;
    }

    public function removePricingGroup(PricingGroupInterface $group): SpecialOfferInterface
    {
        if ($this->pricingGroups->contains($group)) {
            $this->pricingGroups->removeElement($group);
        }

        return $this;
    }

    public function getCustomerGroups(): Collection
    {
        return $this->customerGroups;
    }

    public function addCustomerGroup(CustomerGroupInterface $group): SpecialOfferInterface
    {
        if (!$this->customerGroups->contains($group)) {
            $this->customerGroups->add($group);
        }

        return $this;
    }

    public function removeCustomerGroup(CustomerGroupInterface $group): SpecialOfferInterface
    {
        if ($this->customerGroups->contains($group)) {
            $this->customerGroups->removeElement($group);
        }

        return $this;
    }

    public function getCountries(): Collection
    {
        return $this->countries;
    }

    public function addCountry(CountryInterface $country): SpecialOfferInterface
    {
        if (!$this->countries->contains($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    public function removeCountry(CountryInterface $country): SpecialOfferInterface
    {
        if ($this->countries->contains($country)) {
            $this->countries->removeElement($country);
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
            static::REL_PRODUCTS,
            static::REL_BRANDS,
            static::REL_PRICING_GROUPS,
            static::REL_CUSTOMER_GROUPS,
            static::REL_COUNTRIES,
        ];
    }

    public static function getEntityTagPrefix(): string
    {
        return 'ekyna_product.special_offer';
    }
}

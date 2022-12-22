<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Bundle\MediaBundle\Model\MediaInterface;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Resource\Copier\CopyInterface;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface ProductInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements RM\TranslatableInterface<ProductTranslationInterface>
 */
interface ProductInterface extends
    VisibilityInterface,
    Cms\ContentSubjectInterface,
    Cms\SeoSubjectInterface,
    Cms\TagsSubjectInterface,
    RM\TranslatableInterface,
    RM\SortableInterface,
    RM\TimestampableInterface,
    RM\TaggedEntityInterface,
    CopyInterface,
    Common\AdjustableInterface,
    Common\MentionSubjectInterface,
    TaxableInterface,
    StockSubjectInterface
{
    public function getType(): ?string;

    public function setType(string $type): ProductInterface;

    public function getMinPrice(): Decimal;

    public function setMinPrice(Decimal $minPrice): ProductInterface;

    /**
     * Returns whether the medias are not contractual.
     */
    public function isNotContractual(): bool;

    /**
     * Sets whether the medias are not contractual.
     */
    public function setNotContractual(bool $notContractual): ProductInterface;

    /**
     * Returns whether offers update is needed.
     */
    public function isPendingOffers(): bool;

    /**
     * Sets whether offers update is needed.
     */
    public function setPendingOffers(bool $pending): ProductInterface;

    /**
     * Returns whether prices update is needed.
     */
    public function isPendingPrices(): bool;

    /**
     * Sets whether prices update is needed.
     */
    public function setPendingPrices(bool $pending): ProductInterface;

    /**
     * Returns the best seller mode.
     */
    public function getBestSeller(): string;

    /**
     * Sets the best seller mode.
     */
    public function setBestSeller(string $mode): ProductInterface;

    /**
     * Returns the cross-selling mode.
     */
    public function getCrossSelling(): string;

    /**
     * Sets the cross-selling mode.
     */
    public function setCrossSelling(string $mode): ProductInterface;

    public function getStatUpdatedAt(): ?DateTimeInterface;

    public function setStatUpdatedAt(?DateTimeInterface $date): ProductInterface;

    /**
     * Returns whether to include brand in full designation and title.
     */
    public function isBrandNaming(): bool;

    /**
     * Sets whether to include brand in full designation and title.
     */
    public function setBrandNaming(bool $naming): ProductInterface;

    /**
     * Returns the (translated) title.
     */
    public function getTitle(): ?string;

    /**
     * Returns the (translated) title.
     */
    public function setTitle(?string $title): ProductInterface;

    /**
     * Returns the (translated) sub-title.
     */
    public function getSubTitle(): ?string;

    /**
     * Returns the (translated) sub-title.
     */
    public function setSubTitle(?string $subTitle): ProductInterface;

    /**
     * Returns the (translated) attributes title.
     */
    public function getAttributesTitle(): ?string;

    /**
     * Returns the (translated) attributes title.
     */
    public function setAttributesTitle(?string $attributesTitle): ProductInterface;

    /**
     * Returns the (translated) full title.
     *
     * @param bool $withBrand Whether to prepend the brand name
     */
    public function getFullTitle(bool $withBrand = false): ?string;

    /**
     * Returns the (translated) description.
     */
    public function getDescription(): ?string;

    /**
     * Returns the (translated) description.
     */
    public function setDescription(?string $description): ProductInterface;

    /**
     * Returns the (translated) slug.
     */
    public function getSlug(): ?string;

    /**
     * Returns the (translated) slug.
     */
    public function setSlug(?string $slug): ProductInterface;

    /**
     * Returns the attributes (auto-generated) designation.
     */
    public function getAttributesDesignation(): ?string;

    /**
     * Sets the attributes (auto-generated) designation.
     */
    public function setAttributesDesignation(?string $attributesDesignation): ProductInterface;

    /**
     * Returns the full designation.
     *
     * @param bool $withBrand Whether to prepend the brand name
     */
    public function getFullDesignation(bool $withBrand = false): ?string;

    public function getBrand(): ?BrandInterface;

    public function setBrand(BrandInterface $brand): ProductInterface;

    public function getPricingGroup(): ?PricingGroupInterface;

    public function setPricingGroup(?PricingGroupInterface $pricingGroup): ProductInterface;

    public function getParent(): ?ProductInterface;

    public function setParent(?ProductInterface $parent): ProductInterface;

    /**
     * @return Collection<int, ProductInterface>
     */
    public function getVariants(): Collection;

    public function hasVariant(ProductInterface $variant): bool;

    public function addVariant(ProductInterface $variant): ProductInterface;

    public function removeVariant(ProductInterface $variant): ProductInterface;

    public function getAttributeSet(): ?AttributeSetInterface;

    public function setAttributeSet(?AttributeSetInterface $attributeSet): ProductInterface;

    /**
     * @return Collection<int, ProductAttributeInterface>
     */
    public function getAttributes(): Collection;

    public function hasAttribute(ProductAttributeInterface $attribute): bool;

    public function addAttribute(ProductAttributeInterface $attribute): ProductInterface;

    public function removeAttribute(ProductAttributeInterface $attribute): ProductInterface;

    /**
     * @return Collection<int, OptionGroupInterface>
     */
    public function getOptionGroups(): Collection;

    public function hasOptionGroups(): bool;

    public function hasOptionGroup(OptionGroupInterface $group): bool;

    public function addOptionGroup(OptionGroupInterface $group): ProductInterface;

    public function removeOptionGroup(OptionGroupInterface $group): ProductInterface;

    /**
     * @param Collection<int, OptionGroupInterface> $groups
     *
     * @internal
     */
    public function setOptionGroups(Collection $groups): ProductInterface;

    /**
     * @param array<int> $exclude
     */
    public function hasRequiredOptionGroup(array $exclude = []): bool;

    /**
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     * @param bool       $bundle  Whether to return bundle slots option groups.
     *
     * @return array<OptionGroupInterface>
     */
    public function resolveOptionGroups(bool|array $exclude = [], bool $bundle = false): array;

    /**
     * @return Collection<int, BundleSlotInterface>
     */
    public function getBundleSlots(): Collection;

    public function hasBundleSlot(BundleSlotInterface $slot): bool;

    public function addBundleSlot(BundleSlotInterface $slot): ProductInterface;

    public function removeBundleSlot(BundleSlotInterface $slot): ProductInterface;

    /**
     * @param Collection<int, BundleSlotInterface> $slots
     *
     * @internal
     */
    public function setBundleSlots(Collection $slots): ProductInterface;

    /**
     * @return Collection<int, ComponentInterface>
     */
    public function getComponents(): Collection;

    public function hasComponents():bool;

    public function hasComponent(ComponentInterface $component): bool;

    public function addComponent(ComponentInterface $component): ProductInterface;

    public function removeComponent(ComponentInterface $component): ProductInterface;

    /**
     * @param Collection<int, ComponentInterface> $components
     *
     * @internal
     */
    public function setComponents(Collection $components): ProductInterface;

    /**
     * @return Collection<int, CrossSellingInterface>
     */
    public function getCrossSellings(): Collection;

    public function hasCrossSellings(): bool;

    public function hasCrossSelling(CrossSellingInterface $crossSelling): bool;

    public function addCrossSelling(CrossSellingInterface $crossSelling): ProductInterface;

    public function removeCrossSelling(CrossSellingInterface $crossSelling): ProductInterface;

    /**
     * @param Collection<int, CrossSellingInterface> $crossSellings
     *
     * @internal
     */
    public function setCrossSellings(Collection $crossSellings): ProductInterface;

    /**
     * @return Collection<int, SpecialOfferInterface>
     */
    public function getSpecialOffers(): Collection;

    public function hasSpecialOffer(SpecialOfferInterface $offer): bool;

    public function addSpecialOffer(SpecialOfferInterface $offer): ProductInterface;

    public function removeSpecialOffer(SpecialOfferInterface $offer): ProductInterface;

    /**
     * @param Collection<int, SpecialOfferInterface> $offers
     *
     * @internal
     */
    public function setSpecialOffers(Collection $offers): ProductInterface;

    /**
     * @return Collection<int, PricingInterface>
     */
    public function getPricings(): Collection;

    public function hasPricing(PricingInterface $pricing): bool;

    public function addPricing(PricingInterface $pricing): ProductInterface;

    public function removePricing(PricingInterface $pricing): ProductInterface;

    /**
     * @param Collection<int, PricingInterface> $pricings
     *
     * @internal
     */
    public function setPricings(Collection $pricings): ProductInterface;

    /**
     * @return Collection<int, CategoryInterface>
     */
    public function getCategories(): Collection;

    public function hasCategory(CategoryInterface $category): bool;

    public function addCategory(CategoryInterface $category): ProductInterface;

    public function removeCategory(CategoryInterface $category): ProductInterface;

    /**
     * @param Collection<int, CategoryInterface> $categories
     */
    public function setCategories(Collection $categories): ProductInterface;

    /**
     * @return Collection<int, CustomerGroupInterface>
     */
    public function getCustomerGroups(): Collection;

    public function hasCustomerGroup(CustomerGroupInterface $group): bool;

    public function addCustomerGroup(CustomerGroupInterface $group): ProductInterface;

    public function removeCustomerGroup(CustomerGroupInterface $group): ProductInterface;

    /**
     * @param Collection<int, CustomerGroupInterface> $groups
     */
    public function setCustomerGroups(Collection $groups): ProductInterface;

    public function hasMedia(ProductMediaInterface $media): bool;

    /**
     * @param array<string> $types To filter media by type(s)
     *
     * @return Collection<int, ProductMediaInterface>
     */
    public function getMedias(array $types = []): Collection;

    public function addMedia(ProductMediaInterface $media): ProductInterface;

    public function removeMedia(ProductMediaInterface $media): ProductInterface;

    public function hasReference(ProductReferenceInterface $reference): bool;

    public function addReference(ProductReferenceInterface $reference): ProductInterface;

    public function removeReference(ProductReferenceInterface $reference): ProductInterface;

    /**
     * @return Collection<int, ProductReferenceInterface>
     */
    public function getReferences(): Collection;

    /**
     * Returns the reference for the given type.
     */
    public function getReferenceByType(string $type): ?string;

    public function hasMention(ProductMentionInterface $mention): bool;

    public function addMention(ProductMentionInterface $mention): ProductInterface;

    public function removeMention(ProductMentionInterface $mention): ProductInterface;

    /**
     * Returns the product's main image.
     */
    public function getImage(): ?MediaInterface;

    /**
     * @return Collection<int, MediaInterface>
     */
    public function getImages(bool $withChildren = true, int $limit = 5): Collection;

    /**
     * @return Collection<int, MediaInterface>
     */
    public function getFiles(bool $withChildren = false, int $limit = 5): Collection;

    /**
     * Returns the variant uniqueness signature.
     */
    public function getUniquenessSignature(): string;
}

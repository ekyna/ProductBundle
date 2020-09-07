<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Bundle\MediaBundle\Model\MediaInterface;
use Ekyna\Bundle\ProductBundle\Entity\ProductMention;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface ProductInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method ProductTranslationInterface translate($locale = null, $create = false)
 * @method Collection|ProductTranslationInterface[] getTranslations()
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
    Common\AdjustableInterface,
    Common\MentionSubjectInterface,
    TaxableInterface,
    StockSubjectInterface
{
    /**
     * Returns the parent.
     *
     * @return ProductInterface
     */
    public function getParent();

    /**
     * Sets the parent.
     *
     * @param ProductInterface $parent
     *
     * @return $this|ProductInterface
     */
    public function setParent(ProductInterface $parent = null);

    /**
     * Returns the variants.
     *
     * @return Collection|ProductInterface[]
     */
    public function getVariants();

    /**
     * Returns whether the parent has the given variant or not.
     *
     * @param ProductInterface $variant
     *
     * @return bool
     */
    public function hasVariant(ProductInterface $variant);

    /**
     * Adds the variant.
     *
     * @param ProductInterface $variant
     *
     * @return $this|ProductInterface
     */
    public function addVariant(ProductInterface $variant);

    /**
     * Removes the variant.
     *
     * @param ProductInterface $variant
     *
     * @return $this|ProductInterface
     */
    public function removeVariant(ProductInterface $variant);

    /**
     * Returns the attribute set.
     *
     * @return AttributeSetInterface
     */
    public function getAttributeSet();

    /**
     * Sets the attribute set.
     *
     * @param AttributeSetInterface $attributeSet
     *
     * @return $this|ProductInterface
     */
    public function setAttributeSet(AttributeSetInterface $attributeSet = null);

    /**
     * Returns the attributes.
     *
     * @return Collection|ProductAttributeInterface[]
     */
    public function getAttributes();

    /**
     * Returns whether the product has the given attribute or not.
     *
     * @param ProductAttributeInterface $attribute
     *
     * @return bool
     */
    public function hasAttribute(ProductAttributeInterface $attribute);

    /**
     * Adds the attribute.
     *
     * @param ProductAttributeInterface $attribute
     *
     * @return $this|ProductInterface
     */
    public function addAttribute(ProductAttributeInterface $attribute);

    /**
     * Removes the attribute.
     *
     * @param ProductAttributeInterface $attribute
     *
     * @return $this|ProductInterface
     */
    public function removeAttribute(ProductAttributeInterface $attribute);

    /**
     * Sets the attributes.
     *
     * @param Collection|ProductAttributeInterface[] $attributes
     *
     * @return $this|ProductInterface
     */
    //public function setAttributes(Collection $attributes);

    /**
     * Returns the option groups.
     *
     * @return Collection|OptionGroupInterface[]
     */
    public function getOptionGroups();

    /**
     * Returns whether the product has given option groups or not.
     *
     * @return bool
     */
    public function hasOptionGroups();

    /**
     * Returns whether the product has the given option group or not.
     *
     * @param OptionGroupInterface $group
     *
     * @return bool
     */
    public function hasOptionGroup(OptionGroupInterface $group);

    /**
     * Adds the option group.
     *
     * @param OptionGroupInterface $group
     *
     * @return $this|ProductInterface
     */
    public function addOptionGroup(OptionGroupInterface $group);

    /**
     * Removes the option group.
     *
     * @param OptionGroupInterface $group
     *
     * @return $this|ProductInterface
     */
    public function removeOptionGroup(OptionGroupInterface $group);

    /**
     * Sets the option groups.
     *
     * @param Collection|OptionGroupInterface[] $options
     *
     * @return $this|ProductInterface
     * @internal
     */
    public function setOptionGroups(Collection $options);

    /**
     * Returns whether the product has at least one required option group.
     *
     * @param array $exclude The excluded option group ids.
     *
     * @return bool
     */
    public function hasRequiredOptionGroup(array $exclude = []): bool;

    /**
     * Returns the resolved option groups.
     *
     * @param bool|array $exclude The option group ids to exclude, true to exclude all
     * @param bool       $bundle  Whether to return bundle slots option groups.
     *
     * @return OptionGroupInterface[]
     */
    public function resolveOptionGroups($exclude = [], bool $bundle = false): array;

    /**
     * Returns the bundle slots.
     *
     * @return Collection|BundleSlotInterface[]
     */
    public function getBundleSlots();

    /**
     * Returns whether the product has the given bundle slot or not.
     *
     * @param BundleSlotInterface $slot
     *
     * @return bool
     */
    public function hasBundleSlot(BundleSlotInterface $slot);

    /**
     * Adds the bundle slot.
     *
     * @param BundleSlotInterface $slot
     *
     * @return $this|ProductInterface
     */
    public function addBundleSlot(BundleSlotInterface $slot);

    /**
     * Removes the bundle slot.
     *
     * @param BundleSlotInterface $slot
     *
     * @return $this|ProductInterface
     */
    public function removeBundleSlot(BundleSlotInterface $slot);

    /**
     * Sets the bundle slots.
     *
     * @param Collection|BundleSlotInterface[] $slots
     *
     * @return $this|ProductInterface
     * @internal
     */
    public function setBundleSlots(Collection $slots);

    /**
     * Returns the components.
     *
     * @return Collection|ComponentInterface[]
     */
    public function getComponents();

    /**
     * Returns whether this product has components.
     *
     * @return bool
     */
    public function hasComponents();

    /**
     * Returns whether the product has the given component or not.
     *
     * @param ComponentInterface $component
     *
     * @return bool
     */
    public function hasComponent(ComponentInterface $component);

    /**
     * Adds the component.
     *
     * @param ComponentInterface $component
     *
     * @return $this|ProductInterface
     */
    public function addComponent(ComponentInterface $component);

    /**
     * Removes the component.
     *
     * @param ComponentInterface $component
     *
     * @return $this|ProductInterface
     */
    public function removeComponent(ComponentInterface $component);

    /**
     * Sets the components.
     *
     * @param Collection|ComponentInterface[] $components
     *
     * @return $this|ProductInterface
     * @internal
     */
    public function setComponents(Collection $components);

    /**
     * Returns the cross sellings.
     *
     * @return Collection|CrossSellingInterface[]
     */
    public function getCrossSellings();

    /**
     * Returns whether this product has cross sellings.
     *
     * @return bool
     */
    public function hasCrossSellings();

    /**
     * Returns whether the product has the given cross selling or not.
     *
     * @param CrossSellingInterface $crossSelling
     *
     * @return bool
     */
    public function hasCrossSelling(CrossSellingInterface $crossSelling);

    /**
     * Adds the cross selling.
     *
     * @param CrossSellingInterface $crossSelling
     *
     * @return $this|ProductInterface
     */
    public function addCrossSelling(CrossSellingInterface $crossSelling);

    /**
     * Removes the cross selling.
     *
     * @param CrossSellingInterface $crossSelling
     *
     * @return $this|ProductInterface
     */
    public function removeCrossSelling(CrossSellingInterface $crossSelling);

    /**
     * Sets the cross sellings.
     *
     * @param Collection|CrossSellingInterface[] $crossSellings
     *
     * @return $this|ProductInterface
     * @internal
     */
    public function setCrossSellings(Collection $crossSellings);

    /**
     * Returns the special offers.
     *
     * @return Collection|SpecialOfferInterface[]
     */
    public function getSpecialOffers();

    /**
     * Returns whether the product has the given special offer or not.
     *
     * @param SpecialOfferInterface $offer
     *
     * @return bool
     */
    public function hasSpecialOffer(SpecialOfferInterface $offer);

    /**
     * Adds the special offer.
     *
     * @param SpecialOfferInterface $offer
     *
     * @return $this|ProductInterface
     */
    public function addSpecialOffer(SpecialOfferInterface $offer);

    /**
     * Removes the special offer.
     *
     * @param SpecialOfferInterface $offer
     *
     * @return $this|ProductInterface
     */
    public function removeSpecialOffer(SpecialOfferInterface $offer);

    /**
     * Sets the special offers.
     *
     * @param Collection|SpecialOfferInterface[] $offers
     *
     * @return $this|ProductInterface
     * @internal
     */
    public function setSpecialOffers(Collection $offers);

    /**
     * Returns the pricings.
     *
     * @return Collection|PricingInterface[]
     */
    public function getPricings();

    /**
     * Returns whether the product has the given pricing or not.
     *
     * @param PricingInterface $pricing
     *
     * @return bool
     */
    public function hasPricing(PricingInterface $pricing);

    /**
     * Adds the pricing.
     *
     * @param PricingInterface $pricing
     *
     * @return $this|ProductInterface
     */
    public function addPricing(PricingInterface $pricing);

    /**
     * Removes the pricing.
     *
     * @param PricingInterface $pricing
     *
     * @return $this|ProductInterface
     */
    public function removePricing(PricingInterface $pricing);

    /**
     * Sets the pricings.
     *
     * @param Collection|PricingInterface[] $pricings
     *
     * @return $this|ProductInterface
     * @internal
     */
    public function setPricings(Collection $pricings);

    /**
     * Returns the brand.
     *
     * @return BrandInterface
     */
    public function getBrand();

    /**
     * Sets the brand.
     *
     * @param BrandInterface $brand
     *
     * @return $this|ProductInterface
     */
    public function setBrand(BrandInterface $brand);

    /**
     * Returns the categories.
     *
     * @return Collection|CategoryInterface[]
     */
    public function getCategories();

    /**
     * Returns whether the product has the given given category.
     *
     * @param CategoryInterface $category
     *
     * @return bool
     */
    public function hasCategory(CategoryInterface $category);

    /**
     * Adds the category.
     *
     * @param CategoryInterface $category
     *
     * @return $this|ProductInterface
     */
    public function addCategory(CategoryInterface $category);

    /**
     * Removes the category.
     *
     * @param CategoryInterface $category
     *
     * @return $this|ProductInterface
     */
    public function removeCategory(CategoryInterface $category);

    /**
     * Sets the categories.
     *
     * @param Collection|CategoryInterface[] $categories
     *
     * @return $this|ProductInterface
     */
    public function setCategories(Collection $categories);

    /**
     * Returns the customer groups.
     *
     * @return Collection|CustomerGroupInterface[]
     */
    public function getCustomerGroups();

    /**
     * Returns whether the product has the given given customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return bool
     */
    public function hasCustomerGroup(CustomerGroupInterface $group);

    /**
     * Adds the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return $this|ProductInterface
     */
    public function addCustomerGroup(CustomerGroupInterface $group);

    /**
     * Removes the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return $this|ProductInterface
     */
    public function removeCustomerGroup(CustomerGroupInterface $group);

    /**
     * Sets the customer groups.
     *
     * @param Collection|CustomerGroupInterface[] $groups
     *
     * @return $this|ProductInterface
     */
    public function setCustomerGroups(Collection $groups);

    /**
     * Returns whether or not the product has the given media.
     *
     * @param ProductMediaInterface $media
     *
     * @return bool
     */
    public function hasMedia(ProductMediaInterface $media);

    /**
     * Returns the medias, optionally filtered by (media) types.
     *
     * @param array $types
     *
     * @return Collection|ProductMediaInterface[]
     */
    public function getMedias(array $types = []);

    /**
     * Adds the product media.
     *
     * @param ProductMediaInterface $media
     *
     * @return $this|ProductInterface
     */
    public function addMedia(ProductMediaInterface $media);

    /**
     * Removes the product media.
     *
     * @param ProductMediaInterface $media
     *
     * @return $this|ProductInterface
     */
    public function removeMedia(ProductMediaInterface $media);

    /**
     * Returns whether the medias are not contractual.
     *
     * @return bool
     */
    public function isNotContractual(): bool;

    /**
     * Sets whether the medias are not contractual.
     *
     * @param bool $notContractual
     *
     * @return $this|ProductInterface
     */
    public function setNotContractual(bool $notContractual): ProductInterface;

    /**
     * Returns whether or not the product has the given reference.
     *
     * @param ProductReferenceInterface $reference
     *
     * @return bool
     */
    public function hasReference(ProductReferenceInterface $reference);

    /**
     * Adds the product reference.
     *
     * @param ProductReferenceInterface $reference
     *
     * @return $this|ProductInterface
     */
    public function addReference(ProductReferenceInterface $reference);

    /**
     * Removes the product reference.
     *
     * @param ProductReferenceInterface $reference
     *
     * @return $this|ProductInterface
     */
    public function removeReference(ProductReferenceInterface $reference);

    /**
     * Returns the references.
     *
     * @return Collection|ProductReferenceInterface[]
     */
    public function getReferences();

    /**
     * Returns the reference for the given type.
     *
     * @param string $type
     *
     * @return null|string
     */
    public function getReferenceByType($type);

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType();

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return $this|ProductInterface
     */
    public function setType(string $type);

    /**
     * Returns the (translated) title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Returns the (translated) title.
     *
     * @param string $title
     *
     * @return $this|ProductInterface
     */
    public function setTitle(string $title);

    /**
     * Returns the (translated) sub title.
     *
     * @return string
     */
    public function getSubTitle();

    /**
     * Returns the (translated) subTitle.
     *
     * @param string $subTitle
     *
     * @return $this|ProductInterface
     */
    public function setSubTitle(string $subTitle);

    /**
     * Returns the (translated) attributes title.
     *
     * @return string
     */
    public function getAttributesTitle();

    /**
     * Returns the (translated) attributesTitle.
     *
     * @param string $attributesTitle
     *
     * @return $this|ProductInterface
     */
    public function setAttributesTitle(string $attributesTitle);

    /**
     * Returns the (translated) full title.
     *
     * @param bool $withBrand Whether to prepend the brand name
     *
     * @return string
     */
    public function getFullTitle($withBrand = false);

    /**
     * Returns the (translated) description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Returns the (translated) description.
     *
     * @param string $description
     *
     * @return $this|ProductInterface
     */
    public function setDescription(string $description);

    /**
     * Returns the (translated) slug.
     *
     * @return string
     */
    public function getSlug();

    /**
     * Returns the (translated) slug.
     *
     * @param string $slug
     *
     * @return $this|ProductInterface
     */
    public function setSlug(string $slug);

    /**
     * Returns the attributes (auto-generated) designation.
     *
     * @return string
     */
    public function getAttributesDesignation();

    /**
     * Sets the attributes (auto-generated) designation.
     *
     * @param string $attributesDesignation
     *
     * @return $this|ProductInterface
     */
    public function setAttributesDesignation($attributesDesignation);

    /**
     * Returns whether to include brand in full designation an title.
     *
     * @return bool
     */
    public function isBrandNaming(): bool;

    /**
     * Sets whether to include brand in full designation an title.
     *
     * @param bool $naming
     *
     * @return $this|ProductInterface
     */
    public function setBrandNaming(bool $naming): ProductInterface;

    /**
     * Returns the full designation.
     *
     * @param bool $withBrand Whether to prepend the brand name
     *
     * @return string
     */
    public function getFullDesignation($withBrand = false);

    /**
     * Returns the minimum price.
     *
     * @return float
     */
    public function getMinPrice();

    /**
     * Sets the minimum price.
     *
     * @param float $minPrice
     *
     * @return $this|ProductInterface
     */
    public function setMinPrice($minPrice);

    /**
     * Returns whether offers update is needed.
     *
     * @return bool
     */
    public function isPendingOffers();

    /**
     * Sets whether offers update is needed.
     *
     * @param bool $pending
     *
     * @return $this|ProductInterface
     */
    public function setPendingOffers($pending);

    /**
     * Returns whether prices update is needed.
     *
     * @return bool
     */
    public function isPendingPrices();

    /**
     * Sets whether prices update is needed.
     *
     * @param bool $pending
     *
     * @return $this|ProductInterface
     */
    public function setPendingPrices($pending);

    /**
     * Returns the "released at" date.
     *
     * @return \DateTime
     */
    public function getReleasedAt();

    /**
     * Sets the "released at" date.
     *
     * @param \DateTime $date
     *
     * @return $this|ProductInterface
     */
    public function setReleasedAt(\DateTime $date = null);

    /**
     * Returns the best seller mode.
     *
     * @return int
     */
    public function getBestSeller();

    /**
     * Sets the best seller mode.
     *
     * @param int $value
     *
     * @return $this|ProductInterface
     */
    public function setBestSeller(int $value);

    /**
     * Returns the cross selling mode.
     *
     * @return int
     */
    public function getCrossSelling();

    /**
     * Sets the cross selling mode.
     *
     * @param int $value
     *
     * @return $this|ProductInterface
     */
    public function setCrossSelling(int $value);

    /**
     * Returns the "stat updated at" datetime.
     *
     * @return \DateTime
     */
    public function getStatUpdatedAt();

    /**
     * Sets the "stat updated at" datetime.
     *
     * @param \DateTime $date
     *
     * @return $this|ProductInterface
     */
    public function setStatUpdatedAt(\DateTime $date = null);

    /**
     * Returns whether this product has the given mention.
     *
     * @param ProductMention $mention
     *
     * @return bool
     */
    public function hasMention(ProductMention $mention): bool;

    /**
     * Adds the mention.
     *
     * @param ProductMention $mention
     *
     * @return $this|ProductInterface
     */
    public function addMention(ProductMention $mention): ProductInterface;

    /**
     * Removes the mention.
     *
     * @param ProductMention $mention
     *
     * @return $this|ProductInterface
     */
    public function removeMention(ProductMention $mention): ProductInterface;

    /**
     * Returns the product's main image.
     *
     * @return MediaInterface|null
     */
    public function getImage(): ?MediaInterface;

    /**
     * Returns the product images.
     *
     * @param bool $withChildren
     * @param int  $limit
     *
     * @return Collection|MediaInterface[]
     */
    public function getImages(bool $withChildren = true, int $limit = 5);

    /**
     * Returns the product files.
     *
     * @param bool $withChildren
     * @param int  $limit
     *
     * @return Collection|\Ekyna\Bundle\MediaBundle\Model\MediaInterface[]
     */
    public function getFiles(bool $withChildren = false, int $limit = 5);

    /**
     * Returns the variant uniqueness signature.
     *
     * @return string
     */
    public function getUniquenessSignature();
}

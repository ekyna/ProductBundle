<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Component\Commerce\Common\Model\AdjustableInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface ProductInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method ProductTranslationInterface translate($locale = null, $create = false)
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
    AdjustableInterface,
    TaxableInterface,
    StockSubjectInterface,
    SubjectInterface
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
     * @return ArrayCollection|ProductInterface[]
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
     * @return ArrayCollection|ProductAttributeInterface[]
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
     * @param ArrayCollection|ProductAttributeInterface[] $attributes
     *
     * @return $this|ProductInterface
     */
    //public function setAttributes(ArrayCollection $attributes);

    /**
     * Returns the option groups.
     *
     * @return ArrayCollection|OptionGroupInterface[]
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
     * @param ArrayCollection|OptionGroupInterface[] $options
     *
     * @return $this|ProductInterface
     * @internal
     */
    public function setOptionGroups(ArrayCollection $options);

    /**
     * Returns whether the product has at least one required option group.
     *
     * @return bool
     */
    public function hasRequiredOptionGroup();

    /**
     * Returns the bundle slots.
     *
     * @return ArrayCollection|BundleSlotInterface[]
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
     * @param ArrayCollection|BundleSlotInterface[] $slots
     *
     * @return $this|ProductInterface
     * @internal
     */
    public function setBundleSlots(ArrayCollection $slots);

    /**
     * Returns the special offers.
     *
     * @return ArrayCollection|SpecialOfferInterface[]
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
     * @param ArrayCollection|SpecialOfferInterface[] $offers
     *
     * @return $this|ProductInterface
     * @internal
     */
    public function setSpecialOffers(ArrayCollection $offers);

    /**
     * Returns the pricings.
     *
     * @return ArrayCollection|PricingInterface[]
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
     * @param ArrayCollection|PricingInterface[] $pricings
     *
     * @return $this|ProductInterface
     * @internal
     */
    public function setPricings(ArrayCollection $pricings);

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
     * @return ArrayCollection|CategoryInterface[]
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
     * @param ArrayCollection|CategoryInterface[] $categories
     *
     * @return $this|ProductInterface
     */
    public function setCategories(ArrayCollection $categories);

    /**
     * Returns the customer groups.
     *
     * @return ArrayCollection|CustomerGroupInterface[]
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
     * @param ArrayCollection|CustomerGroupInterface[] $groups
     *
     * @return $this|ProductInterface
     */
    public function setCustomerGroups(ArrayCollection $groups);

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
     * @return ArrayCollection|ProductMediaInterface[]
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
     * @return ArrayCollection|ProductReferenceInterface[]
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
    public function setType($type);

    /**
     * Returns the (translated) title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Returns the (translated) sub title.
     *
     * @return string
     */
    public function getSubTitle();

    /**
     * Returns the (translated) attributes title.
     *
     * @return string
     */
    public function getAttributesTitle();

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
     * Returns the (translated) slug.
     *
     * @return string
     */
    public function getSlug();

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return $this|ProductInterface
     */
    public function setDesignation($designation);

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
     * Returns the full designation.
     *
     * @param bool $withBrand Whether to prepend the brand name
     *
     * @return string
     */
    public function getFullDesignation($withBrand = false);

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return $this|ProductInterface
     */
    public function setReference($reference);

    /**
     * Sets the net price.
     *
     * @param float $netPrice
     *
     * @return $this|ProductInterface
     */
    public function setNetPrice($netPrice);

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
     * Sets the weight (kilograms).
     *
     * @param float $weight
     *
     * @return $this|ProductInterface
     */
    public function setWeight($weight);

    /**
     * Sets the height (millimeters).
     *
     * @param int $height
     *
     * @return $this|ProductInterface
     */
    public function setHeight($height);

    /**
     * Sets the width (millimeters).
     *
     * @param int $width
     *
     * @return $this|ProductInterface
     */
    public function setWidth($width);

    /**
     * Sets the depth (millimeters).
     *
     * @param int $depth
     *
     * @return $this|ProductInterface
     */
    public function setDepth($depth);

    /**
     * Sets the quantity unit.
     *
     * @param string $unit
     *
     * @return $this|ProductInterface
     */
    public function setUnit($unit);

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
     * Returns the product images.
     *
     * @param bool                 $withChildren
     * @param int                  $limit
     * @param ArrayCollection|null $images
     *
     * @return ArrayCollection|\Ekyna\Bundle\MediaBundle\Model\MediaInterface[]
     */
    public function getImages($withChildren = true, $limit = 5, ArrayCollection $images = null);

    /**
     * Returns the product files.
     *
     * @param bool                 $withChildren
     * @param int                  $limit
     * @param ArrayCollection|null $images
     *
     * @return ArrayCollection|\Ekyna\Bundle\MediaBundle\Model\MediaInterface[]
     */
    public function getFiles($withChildren = false, $limit = 5, ArrayCollection $images = null);

    /**
     * Returns the variant uniqueness signature.
     *
     * @return string
     */
    public function getUniquenessSignature();
}

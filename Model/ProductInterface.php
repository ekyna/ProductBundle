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
    Cms\ContentSubjectInterface,
    Cms\SeoSubjectInterface,
    Cms\TagsSubjectInterface,
    RM\TranslatableInterface,
    RM\TimestampableInterface,
    RM\TaggedEntityInterface,
    AdjustableInterface,
    TaxableInterface,
    StockSubjectInterface,
    SubjectInterface
{
    /**
     * Sets the id.
     *
     * @param int $id
     *
     * @return $this|ProductInterface
     */
    public function setId($id);

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
     * @return ArrayCollection|AttributeInterface[]
     */
    public function getAttributes();

    /**
     * Returns whether the product has the given attribute or not.
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function hasAttribute(AttributeInterface $attribute);

    /**
     * Adds the attribute.
     *
     * @param AttributeInterface $attribute
     *
     * @return $this|ProductInterface
     */
    public function addAttribute(AttributeInterface $attribute);

    /**
     * Removes the attribute.
     *
     * @param AttributeInterface $attribute
     *
     * @return $this|ProductInterface
     */
    public function removeAttribute(AttributeInterface $attribute);

    /**
     * Sets the attributes.
     *
     * @param ArrayCollection|AttributeInterface[] $attributes
     *
     * @return $this|ProductInterface
     */
    public function setAttributes(ArrayCollection $attributes);

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
     * @param ArrayCollection|BundleSlotInterface[] $bundleSlots
     *
     * @return $this|ProductInterface
     * @internal
     */
    public function setBundleSlots(ArrayCollection $bundleSlots);

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
     * Returns the visible.
     *
     * @return bool
     */
    public function isVisible();

    /**
     * Sets the visible.
     *
     * @param bool $visible
     *
     * @return $this|ProductInterface
     */
    public function setVisible($visible);

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference();

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return $this|ProductInterface
     */
    public function setReference($reference);

    /**
     * Returns the geocode.
     *
     * @return string
     */
    public function getGeocode();

    /**
     * Sets the geocode.
     *
     * @param string $code
     *
     * @return $this|ProductInterface
     */
    public function setGeocode($code);

    /**
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice();

    /**
     * Sets the net price.
     *
     * @param float $netPrice
     *
     * @return $this|ProductInterface
     */
    public function setNetPrice($netPrice);

    /**
     * Returns the weight (kilograms).
     *
     * @return float
     */
    public function getWeight();

    /**
     * Sets the weight (kilograms).
     *
     * @param float $weight
     *
     * @return $this|ProductInterface
     */
    public function setWeight($weight);

    /**
     * Returns the position (variant ordering).
     *
     * @return int
     */
    public function getPosition();

    /**
     * Sets the position (variant ordering).
     *
     * @param int $position
     *
     * @return $this|ProductInterface
     */
    public function setPosition($position);

    /**
     * Returns the "released at" date.
     *
     * @return \DateTime
     */
    public function getReleasedAt();

    /**
     * Sets the "released at" date.
     *
     * @param \DateTime $releasedAt
     *
     * @return $this|ProductInterface
     */
    public function setReleasedAt(\DateTime $releasedAt = null);

    /**
     * Returns the variant uniqueness signature.
     *
     * @return string
     */
    public function getUniquenessSignature();
}

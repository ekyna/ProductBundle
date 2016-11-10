<?php

namespace Ekyna\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Component\Commerce\Pricing\Model as PricingModel;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Resource\Model as ResourceModel;

/**
 * Interface ProductInterface
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductInterface extends
    Cms\ContentSubjectInterface,
    Cms\SeoSubjectInterface,
    ResourceModel\ResourceInterface,
    ResourceModel\TranslatableInterface,
    ResourceModel\TimestampableInterface ,
    PricingModel\TaxableInterface,
    StockSubjectInterface
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
     * Returns whether the parent has the variant or not.
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
     * Returns whether the product has the attribute or not.
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
     * Returns whether the product has the option group or not.
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
     * Sets the option  groups.
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
     * Returns whether the product has the bundle slot or not.
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
     * Returns the category.
     *
     * @return CategoryInterface
     */
    public function getCategory();

    /**
     * Sets the category.
     *
     * @param CategoryInterface $category
     *
     * @return $this|ProductInterface
     */
    public function setCategory(CategoryInterface $category);

    /**
     * Returns whether or not the product has the image.
     *
     * @param ProductImageInterface $image
     *
     * @return bool
     */
    public function hasImage(ProductImageInterface $image);

    /**
     * Adds the product image.
     *
     * @param ProductImageInterface $image
     *
     * @return $this|ProductInterface
     */
    public function addImage(ProductImageInterface $image);

    /**
     * Removes the product image.
     *
     * @param ProductImageInterface $image
     *
     * @return $this|ProductInterface
     */
    public function removeImage(ProductImageInterface $image);

    /**
     * Returns the images.
     *
     * @return ArrayCollection|ProductImageInterface[]
     */
    public function getImages();

    /**
     * Returns whether or not the product has the reference.
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
     * Returns the title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Sets the title.
     *
     * @param string $title
     *
     * @return $this|ProductInterface
     */
    //public function setTitle($title);

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return $this|ProductInterface
     */
    //public function setDescription($description);

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
     * Returns the releasedAt.
     *
     * @return \DateTime
     */
    public function getReleasedAt();

    /**
     * Sets the releasedAt.
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

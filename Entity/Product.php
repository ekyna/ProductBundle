<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectTrait;
use Ekyna\Component\Resource\Model as RM;

/**
 * Class Product
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\ProductTranslationInterface translate($locale = null, $create = false)
 */
class Product extends RM\AbstractTranslatable implements Model\ProductInterface
{
    use Common\AdjustableTrait,
        Cms\ContentSubjectTrait,
        Cms\SeoSubjectTrait,
        RM\TimestampableTrait,
        RM\TaggedEntityTrait,
        StockSubjectTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\ProductInterface
     */
    protected $parent;

    /**
     * @var ArrayCollection|Model\ProductInterface[]
     */
    protected $variants;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Model\AttributeSetInterface
     */
    protected $attributeSet;

    /**
     * @var ArrayCollection|Model\AttributeInterface[]
     */
    protected $attributes;

    /**
     * @var ArrayCollection|Model\OptionGroupInterface[]
     */
    protected $optionGroups;

    /**
     * @var ArrayCollection|Model\BundleSlotInterface[]
     */
    protected $bundleSlots;

    /**
     * @var TaxGroupInterface
     */
    protected $taxGroup;

    /**
     * @var Model\BrandInterface
     */
    protected $brand;

    /**
     * @var ArrayCollection|Model\CategoryInterface[]
     */
    protected $categories;

    /**
     * @var ArrayCollection|Model\ProductMediaInterface[]
     */
    protected $medias;

    /**
     * @var ArrayCollection|Model\ProductReferenceInterface[]
     */
    protected $references;

    /**
     * @var string
     */
    protected $designation;

    /**
     * @var string
     */
    protected $attributesDesignation;

    /**
     * @var bool
     */
    protected $visible;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var string
     */
    protected $geocode;

    /**
     * @var float
     */
    protected $netPrice;

    /**
     * @var float
     */
    protected $weight;

    /**
     * @var \DateTime
     */
    protected $releasedAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->categories = new ArrayCollection();
        $this->variants = new ArrayCollection();
        $this->attributes = new ArrayCollection();
        $this->optionGroups = new ArrayCollection();
        $this->bundleSlots = new ArrayCollection();
        $this->medias = new ArrayCollection();
        $this->references = new ArrayCollection();

        $this->visible = true;

        $this->initializeAdjustments();
        $this->initializeStock();
    }

    /**
     * Clones the product.
     */
    public function __clone()
    {
        if ($this->id) {
            $variants = $this->variants;
            $this->variants = new ArrayCollection();
            foreach ($variants as $variant) {
                $this->addVariant(clone $variant);
            }

            $attributes = $this->attributes;
            $this->attributes = new ArrayCollection();
            foreach ($attributes as $attribute) {
                $this->addAttribute(clone $attribute);
            }

            $optionGroups = $this->optionGroups;
            $this->optionGroups = new ArrayCollection();
            foreach ($optionGroups as $optionGroup) {
                $this->addOptionGroup(clone $optionGroup);
            }

            $bundleSlots = $this->bundleSlots;
            $this->bundleSlots = new ArrayCollection();
            foreach ($bundleSlots as $bundleSlot) {
                $this->addBundleSlot(clone $bundleSlot);
            }

            $this->seo = clone $this->seo;

            // TODO medias ?
            // TODO adjustment ?
        }
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        $designation = $this->getDesignation();

        // Variant : parent designation + variant designation
        if ($this->type === Model\ProductTypes::TYPE_VARIANT) {
            if (0 == strlen($designation)) {
                // Fallback to auto-generated designation
                $designation = $this->getAttributesDesignation();
            }
            $designation = sprintf('%s %s', $this->parent->getDesignation(), $designation);
        }

        return sprintf('%s %s', $this->brand, $designation);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function setParent(Model\ProductInterface $parent = null)
    {
        if (null !== $this->parent && $parent !== $this->parent) {
            $this->parent->removeVariant($this);
        }

        $this->parent = $parent;

        if (null !== $parent) {
            $parent->addVariant($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * @inheritdoc
     */
    public function hasVariant(Model\ProductInterface $variant)
    {
        return $this->variants->contains($variant);
    }

    /**
     * @inheritdoc
     */
    public function addVariant(Model\ProductInterface $variant)
    {
        if (!$this->hasVariant($variant)) {
            $this->variants->add($variant);
            if ($variant->getParent() !== $this) {
                $variant->setParent($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeVariant(Model\ProductInterface $variant)
    {
        if ($this->hasVariant($variant)) {
            $this->variants->removeElement($variant);
            $variant->setParent(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeSet()
    {
        return $this->attributeSet;
    }

    /**
     * @inheritdoc
     */
    public function setAttributeSet(Model\AttributeSetInterface $attributeSet = null)
    {
        $this->attributeSet = $attributeSet;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function hasAttribute(Model\AttributeInterface $attribute)
    {
        return $this->attributes->contains($attribute);
    }

    /**
     * @inheritdoc
     */
    public function addAttribute(Model\AttributeInterface $attribute)
    {
        if (!$this->hasAttribute($attribute)) {
            $this->attributes->add($attribute);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAttribute(Model\AttributeInterface $attribute)
    {
        if ($this->hasAttribute($attribute)) {
            $this->attributes->removeElement($attribute);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setAttributes(ArrayCollection $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOptionGroups()
    {
        return $this->optionGroups;
    }

    /**
     * @inheritdoc
     */
    public function hasOptionGroup(Model\OptionGroupInterface $group)
    {
        return $this->optionGroups->contains($group);
    }

    /**
     * @inheritdoc
     */
    public function addOptionGroup(Model\OptionGroupInterface $group)
    {
        if (!$this->hasOptionGroup($group)) {
            $group->setProduct($this);
            $this->optionGroups->add($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeOptionGroup(Model\OptionGroupInterface $group)
    {
        if ($this->hasOptionGroup($group)) {
            $group->setProduct(null);
            $this->optionGroups->removeElement($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setOptionGroups(ArrayCollection $optionGroups)
    {
        foreach ($this->optionGroups as $group) {
            $this->removeOptionGroup($group);
        }

        foreach ($optionGroups as $group) {
            $this->addOptionGroup($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBundleSlots()
    {
        return $this->bundleSlots;
    }

    /**
     * @inheritdoc
     */
    public function hasBundleSlot(Model\BundleSlotInterface $slot)
    {
        return $this->bundleSlots->contains($slot);
    }

    /**
     * @inheritdoc
     */
    public function addBundleSlot(Model\BundleSlotInterface $slot)
    {
        if (!$this->hasBundleSlot($slot)) {
            $slot->setBundle($this);
            $this->bundleSlots->add($slot);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeBundleSlot(Model\BundleSlotInterface $slot)
    {
        if ($this->hasBundleSlot($slot)) {
            $slot->setBundle(null);
            $this->bundleSlots->removeElement($slot);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setBundleSlots(ArrayCollection $bundleSlots)
    {
        foreach ($this->bundleSlots as $slot) {
            $this->removeBundleSlot($slot);
        }

        foreach ($bundleSlots as $slot) {
            $this->addBundleSlot($slot);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxGroup()
    {
        return $this->taxGroup;
    }

    /**
     * @inheritdoc
     */
    public function setTaxGroup(TaxGroupInterface $group = null)
    {
        $this->taxGroup = $group;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @inheritdoc
     */
    public function setBrand(Model\BrandInterface $brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @inheritdoc
     */
    public function hasCategory(Model\CategoryInterface $category)
    {
        return $this->categories->contains($category);
    }

    /**
     * @inheritdoc
     */
    public function addCategory(Model\CategoryInterface $category)
    {
        if (!$this->hasCategory($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCategory(Model\CategoryInterface $category)
    {
        if ($this->hasCategory($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCategories(ArrayCollection $categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasMedia(Model\ProductMediaInterface $media)
    {
        return $this->medias->contains($media);
    }

    /**
     * @inheritdoc
     */
    public function addMedia(Model\ProductMediaInterface $media)
    {
        if (!$this->hasMedia($media)) {
            $media->setProduct($this);
            $this->medias->add($media);
            // TODO ??? $this->setUpdatedAt(new \DateTime());
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeMedia(Model\ProductMediaInterface $media)
    {
        if ($this->hasMedia($media)) {
            $media->setProduct(null);
            $this->medias->removeElement($media);
            // TODO ??? $this->setUpdatedAt(new \DateTime());
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMedias(array $types = [])
    {
        if (!empty($types)) {
            foreach ($types as $type) {
                MediaTypes::isValid($type, true);
            }

            return $this->medias->filter(function(Model\ProductMediaInterface $media) use ($types) {
                return in_array($media->getMedia()->getType(), $types);
            });
        }

        return $this->medias;
    }

    /**
     * @inheritdoc
     */
    public function hasReference(Model\ProductReferenceInterface $reference)
    {
        return $this->references->contains($reference);
    }

    /**
     * @inheritdoc
     */
    public function addReference(Model\ProductReferenceInterface $reference)
    {
        if (!$this->hasReference($reference)) {
            $reference->setProduct($this);
            $this->references->add($reference);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeReference(Model\ProductReferenceInterface $reference)
    {
        if ($this->hasReference($reference)) {
            $reference->setProduct(null);
            $this->references->removeElement($reference);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        // Variant : parent title + variant title
        if ($this->type === Model\ProductTypes::TYPE_VARIANT) {
            if (0 == strlen($title = $this->translate()->getTitle())) {
                // Fallback to auto-generated title
                $title = $this->getAttributesTitle();
            }
            return sprintf('%s %s', $this->parent->getTitle(), $title);
        }

        return $this->translate()->getTitle();
    }

    /**
     * @inheritdoc
     */
    public function getAttributesTitle()
    {
        return $this->translate()->getAttributesTitle();
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        // Variant : fallback to parent description
        if ($this->type === Model\ProductTypes::TYPE_VARIANT) {
            return $this->parent->getDescription();
        }

        return $this->translate()->getDescription();
    }

    /**
     * @inheritdoc
     */
    public function getSlug()
    {
        return $this->translate()->getSlug();
    }

    /**
     * @inheritdoc
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * @inheritdoc
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttributesDesignation()
    {
        return $this->attributesDesignation;
    }

    /**
     * @inheritdoc
     */
    public function setAttributesDesignation($attributesDesignation)
    {
        $this->attributesDesignation = $attributesDesignation;

        return $this;
    }

    /**
     * Returns the visible.
     *
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * Sets the visible.
     *
     * @param bool $visible
     *
     * @return Product
     */
    public function setVisible($visible)
    {
        $this->visible = (bool)$visible;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @inheritdoc
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGeocode()
    {
        return $this->geocode;
    }

    /**
     * @inheritdoc
     */
    public function setGeocode($code)
    {
        $this->geocode = $code;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @inheritdoc
     */
    public function setNetPrice($netPrice)
    {
        $this->netPrice = $netPrice;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @inheritdoc
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReleasedAt()
    {
        return $this->releasedAt;
    }

    /**
     * @inheritdoc
     */
    public function setReleasedAt(\DateTime $releasedAt = null)
    {
        $this->releasedAt = $releasedAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\ProductAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of ProductAdjustmentInterface.");
        }

        return $this->adjustments->contains($adjustment);
    }

    /**
     * @inheritdoc
     */
    public function addAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\ProductAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of ProductAdjustmentInterface.");
        }

        if (!$this->hasAdjustment($adjustment)) {
            $this->adjustments->add($adjustment);
            $adjustment->setProduct($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAdjustment(Common\AdjustmentInterface $adjustment)
    {
        if (!$adjustment instanceof Model\ProductAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of ProductAdjustmentInterface.");
        }

        if ($this->hasAdjustment($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            //$adjustment->setProduct(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUniquenessSignature()
    {
        Model\ProductTypes::assertVariant($this);

        $ids = [];
        foreach ($this->attributes as $attribute) {
            $ids[] = $attribute->getId();
        }
        sort($ids);

        return implode('-', $ids);
    }

    /**
     * @inheritdoc
     */
    public function getStockState()
    {
        // TODO if bundled or variable, resolve stock state

        return $this->stockState;
    }

    /**
     * @inheritdoc
     */
    public function getInStock()
    {
        // TODO if bundled or variable, resolve stock

        return $this->inStock;
    }

    /**
     * Returns the ordered stock.
     *
     * @return float
     */
    public function getOrderedStock()
    {
        // TODO if bundled or variable, resolve stock

        return $this->orderedStock;
    }

    /**
     * Returns the estimated date of arrival.
     *
     * @return \DateTime
     */
    public function getEstimatedDateOfArrival()
    {
        // TODO if bundled or variable, resolve eda

        return $this->estimatedDateOfArrival;
    }

    /**
     * @inheritdoc
     */
    public function getStockUnitClass()
    {
        return ProductStockUnit::class;
    }

    /**
     * @inheritdoc
     */
    protected function getTranslationClass()
    {
        return ProductTranslation::class;
    }

    /**
     * @inheritdoc
     */
    public static function getEntityTagPrefix()
    {
        return 'ekyna_product.product';
    }
}

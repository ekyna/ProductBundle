<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Bundle\MediaBundle\Model as Media;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model as Pricing;
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
        Cms\TagsSubjectTrait,
        RM\TimestampableTrait,
        RM\TaggedEntityTrait,
        Pricing\TaxableTrait,
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
     * @var Model\AttributeSetInterface
     */
    protected $attributeSet;

    /**
     * @var ArrayCollection|Model\ProductAttributeInterface[]
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
     * @var Model\BrandInterface
     */
    protected $brand;

    /**
     * @var ArrayCollection|Model\CategoryInterface[]
     */
    protected $categories;

    /**
     * @var ArrayCollection|CustomerGroupInterface[]
     */
    protected $customerGroups;

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
    protected $type;

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
    protected $visible = false;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var float
     */
    protected $netPrice = 0;

    /**
     * @var float
     */
    protected $weight = 0;

    /**
     * @var int
     */
    protected $height = 0;

    /**
     * @var int
     */
    protected $width = 0;

    /**
     * @var int
     */
    protected $depth = 0;

    /**
     * @var string
     */
    protected $unit = Common\Units::PIECE;

    /**
     * (Variant sorting)
     *
     * @var int
     */
    protected $position;

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

        $this->attributes = new ArrayCollection();
        $this->bundleSlots = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->medias = new ArrayCollection();
        $this->optionGroups = new ArrayCollection();
        $this->customerGroups = new ArrayCollection();
        $this->references = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->variants = new ArrayCollection();

        $this->initializeAdjustments();
        $this->initializeStock();
    }

    /**
     * Clones the product.
     */
    public function __clone()
    {
        if ($this->id) {

            // ---- ONE TO MANY ----

            // Adjustments
            $adjustments = $this->adjustments->toArray();
            $this->adjustments = new ArrayCollection();
            foreach ($adjustments as $adjustment) {
                $this->addAdjustment(clone $adjustment);
            }

            // Attributes
            $attributes = $this->attributes->toArray();
            $this->attributes = new ArrayCollection();
            foreach ($attributes as $attribute) {
                $this->addAttribute(clone $attribute);
            }

            // Bundle slots
            $bundleSlots = $this->bundleSlots->toArray();
            $this->bundleSlots = new ArrayCollection();
            foreach ($bundleSlots as $bundleSlot) {
                $this->addBundleSlot(clone $bundleSlot);
            }

            // Medias
            $medias = $this->medias->toArray();
            $this->medias = new ArrayCollection();
            foreach ($medias as $media) {
                $this->addMedia(clone $media);
            }

            // Option groups
            $optionGroups = $this->optionGroups->toArray();
            $this->optionGroups = new ArrayCollection();
            foreach ($optionGroups as $optionGroup) {
                $this->addOptionGroup(clone $optionGroup);
            }

            // References
            $this->references = new ArrayCollection();

            // Translations
            $translations = $this->translations->toArray();
            $this->translations = new ArrayCollection();
            foreach ($translations as $translation) {
                $this->addTranslation(clone $translation);
            }

            // Variants
            $variants = $this->variants->toArray();
            $this->variants = new ArrayCollection();
            foreach ($variants as $variant) {
                $this->addVariant(clone $variant);
            }

            // ---- MANY TO MANY ----

            // Categories
            $categories = $this->categories->toArray();
            $this->categories = new ArrayCollection();
            foreach ($categories as $category) {
                $this->addCategory($category);
            }

            // Customer groups
            $customerGroups = $this->customerGroups->toArray();
            $this->customerGroups = new ArrayCollection();
            foreach ($customerGroups as $customerGroup) {
                $this->addCustomerGroup($customerGroup);
            }

            // Tags
            $tags = $this->tags->toArray();
            $this->tags = new ArrayCollection();
            foreach ($tags as $tag) {
                $this->addTag($tag);
            }

            // ---- MANY TO ONE ----

            // Brand is ok
            // Parent is ok
            // Tax group is ok
            // Attribute set is ok

            // ---- BASICS ----

            // Seo
            if ($this->seo) {
                $this->seo = clone $this->seo;
            }
            // Content
            $this->content = null;

            // ---- BASICS ----

            // Reset stock data (but preserve mode)
            $stockMode = $this->stockMode;
            $quoteOnly = $this->quoteOnly;
            $this->initializeStock();
            $this->stockMode = $stockMode;
            $this->quoteOnly = $quoteOnly;

            // Clear critical fields
            $this->id = null;
            $this->designation = null;
            $this->reference = null;
            $this->geocode = null;
            $this->references = new ArrayCollection();
            $this->visible = false;
            //$this->netPrice = 0;
            //$this->weight = 0;
            //$this->releasedAt = null;
        }
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getFullDesignation(true);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier()
    {
        return $this->getId();
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
        if ($this->parent !== $parent) {
            if ($previous = $this->parent) {
                $this->parent = null;
                $previous->removeVariant($this);
            }

            if ($this->parent = $parent) {
                $this->parent->addVariant($this);
            }
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
            $variant->setParent($this);
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
    public function hasAttribute(Model\ProductAttributeInterface $attribute)
    {
        return $this->attributes->contains($attribute);
    }

    /**
     * @inheritdoc
     */
    public function addAttribute(Model\ProductAttributeInterface $attribute)
    {
        if (!$this->hasAttribute($attribute)) {
            $this->attributes->add($attribute);
            $attribute->setProduct($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAttribute(Model\ProductAttributeInterface $attribute)
    {
        if ($this->hasAttribute($attribute)) {
            $this->attributes->removeElement($attribute);
            $attribute->setProduct(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    /*public function setAttributes(ArrayCollection $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }*/

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
    public function hasOptionGroups()
    {
        return 0 < $this->optionGroups->count();
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
            $this->optionGroups->add($group);
            $group->setProduct($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeOptionGroup(Model\OptionGroupInterface $group)
    {
        if ($this->hasOptionGroup($group)) {
            $this->optionGroups->removeElement($group);
            $group->setProduct(null);
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
     * @inheritDoc
     */
    public function hasRequiredOptionGroup()
    {
        foreach( $this->optionGroups as $optionGroup) {
            if ($optionGroup->isRequired()) {
                return true;
            }
        }

        return false;
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
            $this->bundleSlots->add($slot);
            $slot->setBundle($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeBundleSlot(Model\BundleSlotInterface $slot)
    {
        if ($this->hasBundleSlot($slot)) {
            $this->bundleSlots->removeElement($slot);
            $slot->setBundle(null);
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
        foreach ($this->categories as $category) {
            $this->removeCategory($category);
        }

        foreach ($categories as $category) {
            $this->addCategory($category);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }
    /**
     * @inheritdoc
     */
    public function hasCustomerGroup(CustomerGroupInterface $group)
    {
        return $this->customerGroups->contains($group);
    }

    /**
     * @inheritdoc
     */
    public function addCustomerGroup(CustomerGroupInterface $group)
    {
        if (!$this->hasCustomerGroup($group)) {
            $this->customerGroups->add($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCustomerGroup(CustomerGroupInterface $group)
    {
        if ($this->hasCustomerGroup($group)) {
            $this->customerGroups->removeElement($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroups(ArrayCollection $customerGroups)
    {
        foreach ($this->customerGroups as $group) {
            $this->removeCustomerGroup($group);
        }

        foreach ($customerGroups as $group) {
            $this->addCustomerGroup($group);
        }

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
            $this->medias->add($media);
            $media->setProduct($this);
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
            $this->medias->removeElement($media);
            $media->setProduct(null);
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
                Media\MediaTypes::isValid($type, true);
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
            $this->references->add($reference);
            $reference->setProduct($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeReference(Model\ProductReferenceInterface $reference)
    {
        if ($this->hasReference($reference)) {
            $this->references->removeElement($reference);
            $reference->setProduct(null);
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
    public function getReferenceByType($type)
    {
        Model\ProductReferenceTypes::isValid($type, true);

        foreach ($this->references as $reference) {
            if ($reference->getType() === $type) {
                return $reference->getNumber();
            }
        }

        return null;
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
    public function getTitle()
    {
        return $this->translate()->getTitle();
    }

    /**
     * @inheritdoc
     */
    public function getSubTitle()
    {
        return $this->translate()->getSubTitle();
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
    public function getFullTitle($withBrand = false)
    {
        $title = $this->getTitle();

        // Variant : parent title + variant title
        if ($this->type === Model\ProductTypes::TYPE_VARIANT) {
            if (0 == strlen($title)) {
                // Fallback to auto-generated title
                $title = $this->getAttributesTitle();
            }

            $title = sprintf('%s %s', $this->parent->getTitle(), $title);
        }

        // Prepend the brand
        return $withBrand ? sprintf('%s %s', $this->brand->getTitle(), $title) : $title;
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
     * @inheritdoc
     */
    public function getFullDesignation($withBrand = false)
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

        // Prepend the brand
        return $withBrand ? sprintf('%s %s', $this->brand->getName(), $designation) : $designation;
    }

    /**
     * @inheritdoc
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @inheritdoc
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
     * @inheritDoc
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @inheritDoc
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @inheritDoc
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @inheritDoc
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @inheritDoc
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function setPosition($position)
    {
        $this->position = (int)$position;

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
            $adjustment->setProduct(null);
        }

        return $this;
    }

    /**
     * Returns the product images.
     *
     * @param bool                 $withChildren
     * @param int                  $limit
     * @param ArrayCollection|null $images
     *
     * @return ArrayCollection|Media\MediaInterface[]
     */
    public function getImages($withChildren = true, $limit = 5, ArrayCollection $images = null)
    {
        if (null === $images) {
            $images = new ArrayCollection();
        }

        foreach ($this->medias as $pm) {
            $media = $pm->getMedia();
            if ($media->getType() === Media\MediaTypes::IMAGE && !$images->contains($media)) {
                $images->add($media);
                if ($limit <= $images->count()) {
                    break;
                }
            }
        }

        if ($withChildren && $limit > $images->count()) {
            if ($this->type === Model\ProductTypes::TYPE_VARIABLE) {
                /** @var Product $variant TODO */
                foreach ($this->variants as $variant) {
                    $variant->getImages(false, $limit, $images);
                }
            } elseif (in_array($this->type, [Model\ProductTypes::TYPE_BUNDLE, Model\ProductTypes::TYPE_CONFIGURABLE])) {
                foreach ($this->bundleSlots as $slot) {
                    $choices = $slot->getChoices();
                    foreach ($choices as $choice) {
                        $product = $choice->getProduct();
                        if (0 < $product->getMedias()->count()) {
                            foreach ($product->getMedias() as $pm) {
                                $media = $pm->getMedia();
                                if ($media->getType() === Media\MediaTypes::IMAGE && !$images->contains($media)) {
                                    $images->add($media);
                                    if ($limit <= $images->count()) {
                                        break 3;
                                    }
                                    break;
                                }
                            }
                        } elseif ($product->getType() === Model\ProductTypes::TYPE_VARIABLE) {
                            foreach ($product->getVariants() as $variant) {
                                $variant->getImages(false, $limit, $images);
                            }
                        }
                    }
                }
            }
        }

        return $images;
    }

    /**
     * @inheritDoc
     */
    public function isStockCompound()
    {
        return Model\ProductTypes::isParentType($this->type);
    }

    /**
     * @inheritDoc
     */
    public function hasDimensions()
    {
        return !empty($this->width) && !empty($this->height) && !empty($this->depth);
    }

    /**
     * @inheritdoc
     */
    public function getUniquenessSignature()
    {
        Model\ProductTypes::assertVariant($this);

        $values = [];
        foreach ($this->attributes as $pr) {
            $key = $pr->getAttributeSlot()->getAttribute()->getId();
            if (!empty($v = $pr->getValue())) {
                $values[$key] = $v;
            } else {
                $ids = [];
                /** @var Model\AttributeChoiceInterface $c */
                foreach ($pr->getChoices()->toArray() as $c) {
                    $ids[] = $c->getId();
                }
                sort($ids);
                $values[$key] = implode(',', $ids);
            }
        }

        ksort($values);
        $couples = array_map(function($k, $v) {
            return $k.':'.$v;
        }, array_keys($values), $values);

        return md5(implode('-', $couples));
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
    public static function getStockUnitClass()
    {
        return ProductStockUnit::class;
    }

    /**
     * @inheritdoc
     */
    public static function getEntityTagPrefix()
    {
        return 'ekyna_product.product';
    }

    /**
     * @inheritdoc
     */
    static public function getProviderName()
    {
        return ProductProvider::NAME;
    }
}

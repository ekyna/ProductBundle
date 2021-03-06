<?php

namespace Ekyna\Bundle\ProductBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Bundle\MediaBundle\Model as Media;
use Ekyna\Bundle\ProductBundle\Exception\InvalidArgumentException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Stock\Model as Stock;
use Ekyna\Component\Resource\Model as RM;

/**
 * Class Product
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\ProductTranslationInterface translate($locale = null, $create = false)
 * @method Collection|Model\ProductTranslationInterface[] getTranslations()
 */
class Product extends RM\AbstractTranslatable implements Model\ProductInterface
{
    use Cms\ContentSubjectTrait;
    use Cms\SeoSubjectTrait;
    use Cms\TagsSubjectTrait;
    use Common\AdjustableTrait;
    use Common\MentionSubjectTrait;
    use Model\VisibilityTrait;
    use RM\SortableTrait;
    use RM\TaggedEntityTrait;
    use RM\TimestampableTrait;
    use Stock\StockSubjectTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\ProductInterface
     */
    protected $parent;

    /**
     * @var Collection|Model\ProductInterface[]
     */
    protected $variants;

    /**
     * @var Model\AttributeSetInterface
     */
    protected $attributeSet;

    /**
     * @var Collection|Model\ProductAttributeInterface[]
     */
    protected $attributes;

    /**
     * @var Collection|Model\OptionGroupInterface[]
     */
    protected $optionGroups;

    /**
     * @var Collection|Model\BundleSlotInterface[]
     */
    protected $bundleSlots;

    /**
     * @var Collection|Model\ComponentInterface[]
     */
    protected $components;

    /**
     * @var Collection|Model\CrossSellingInterface[]
     */
    protected $crossSellings;

    /**
     * @var Collection|Model\SpecialOfferInterface[]
     */
    protected $specialOffers;

    /**
     * @var Collection|Model\PricingInterface[]
     */
    protected $pricings;

    /**
     * @var Model\BrandInterface
     */
    protected $brand;

    /**
     * @var Collection|Model\CategoryInterface[]
     */
    protected $categories;

    /**
     * @var Collection|CustomerGroupInterface[]
     */
    protected $customerGroups;

    /**
     * @var Collection|Model\ProductMediaInterface[]
     */
    protected $medias;

    /**
     * @var bool
     */
    protected $notContractual = false;

    /**
     * @var Collection|Model\ProductReferenceInterface[]
     */
    protected $references;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $attributesDesignation;

    /**
     * @var bool
     */
    protected $brandNaming = true; // Include brand name in designation and title

    /**
     * @var float
     */
    protected $minPrice = 0;

    /**
     * @var bool
     */
    protected $pendingOffers = true; // Schedule offers update at creation

    /**
     * @var bool
     */
    protected $pendingPrices = true; // Schedule prices update at creation

    /**
     * @var \DateTime
     */
    protected $releasedAt;

    /**
     * @var int
     */
    protected $bestSeller = Model\HighlightModes::MODE_AUTO;

    /**
     * @var int
     */
    protected $crossSelling = Model\HighlightModes::MODE_AUTO;

    /**
     * @var \DateTime
     */
    protected $statUpdatedAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->attributes = new ArrayCollection();
        $this->bundleSlots = new ArrayCollection();
        $this->components = new ArrayCollection();
        $this->specialOffers = new ArrayCollection();
        $this->pricings = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->medias = new ArrayCollection();
        $this->optionGroups = new ArrayCollection();
        $this->customerGroups = new ArrayCollection();
        $this->references = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->variants = new ArrayCollection();

        $this->initializeAdjustments();
        $this->initializeMentions();
        $this->initializeStock();
    }

    /**
     * Clones the product.
     */
    public function __clone()
    {
        parent::__clone();

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

        // Components
        $components = $this->components->toArray();
        $this->components = new ArrayCollection();
        foreach ($components as $component) {
            $this->addComponent(clone $component);
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

        // Variants
        $variants = $this->variants->toArray();
        $this->variants = new ArrayCollection();
        foreach ($variants as $variant) {
            $this->addVariant(clone $variant);
        }

        // Cross sellings
        $crossSellings = $this->crossSellings->toArray();
        $this->crossSellings = new ArrayCollection();
        foreach ($crossSellings as $crossSelling) {
            $this->addCrossSelling(clone $crossSelling);
        }

        // Special offers
        $specialOffers = $this->specialOffers->toArray();
        $this->specialOffers = new ArrayCollection();
        foreach ($specialOffers as $specialOffer) {
            $this->addSpecialOffer(clone $specialOffer);
        }

        // Pricings
        $pricings = $this->pricings->toArray();
        $this->pricings = new ArrayCollection();
        foreach ($pricings as $pricing) {
            $this->addPricing(clone $pricing);
        }

        // Mentions
        $mentions = $this->mentions->toArray();
        $this->mentions = new ArrayCollection();
        foreach ($mentions as $mention) {
            $this->addMention(clone $mention);
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
            $seo = clone $this->seo;
            $this->seo = $seo;
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
        $this->notContractual = true;
        //$this->netPrice = 0;
        //$this->weight = 0;
        //$this->releasedAt = null;
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFullDesignation(true) ?: 'New product';
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
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
    public function setOptionGroups(Collection $optionGroups)
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
    public function hasRequiredOptionGroup(array $exclude = []): bool
    {
        // All types
        foreach ($this->optionGroups as $optionGroup) {
            if (in_array($optionGroup->getId(), $exclude)) {
                continue;
            }

            if ($optionGroup->isRequired()) {
                return true;
            }
        }

        // A variant inherits options from his parent
        if ($this->parent) {
            foreach ($this->parent->getOptionGroups() as $optionGroup) {
                if (in_array($optionGroup->getId(), $exclude)) {
                    continue;
                }

                if ($optionGroup->isRequired()) {
                    return true;
                }
            }
        } elseif ($this->type === Model\ProductTypes::TYPE_BUNDLE) {
            foreach ($this->bundleSlots as $slot) {
                /** @var Model\BundleChoiceInterface $choice */
                $choice = $slot->getChoices()->first();
                if ($choice->getProduct()->hasRequiredOptionGroup($choice->getExcludedOptionGroups())) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function resolveOptionGroups($exclude = [], bool $bundle = false): array
    {
        if (true === $exclude) {
            return [];
        }

        if (!is_array($exclude)) {
            $exclude = [];
        }

        $groups = [];

        if (Model\ProductTypes::isVariantType($this)) {
            foreach ($this->parent->getOptionGroups() as $group) {
                if (in_array($group->getId(), $exclude)) {
                    continue;
                }

                $groups[] = $group;
            }
        } elseif (Model\ProductTypes::isBundleType($this) && $bundle) {
            foreach ($this->bundleSlots as $slot) {
                /** @var Model\BundleChoiceInterface $choice */
                $choice = $slot->getChoices()->first();
                $choiceGroups = $choice->getProduct()->resolveOptionGroups($choice->getExcludedOptionGroups(), true);
                foreach ($choiceGroups as $group) {
                    if (in_array($group->getId(), $exclude)) {
                        continue;
                    }

                    $groups[] = $group;
                }
            }
        }

        foreach ($this->getOptionGroups() as $group) {
            if (in_array($group->getId(), $exclude)) {
                continue;
            }

            $groups[] = $group;
        }

        return $groups;
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
    public function setBundleSlots(Collection $slots)
    {
        foreach ($this->bundleSlots as $slot) {
            $this->removeBundleSlot($slot);
        }

        foreach ($slots as $slot) {
            $this->addBundleSlot($slot);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * @inheritdoc
     */
    public function hasComponents()
    {
        return 0 < $this->components->count();
    }

    /**
     * @inheritdoc
     */
    public function hasComponent(Model\ComponentInterface $component)
    {
        return $this->components->contains($component);
    }

    /**
     * @inheritdoc
     */
    public function addComponent(Model\ComponentInterface $component)
    {
        if (!$this->hasComponent($component)) {
            $this->components->add($component);
            $component->setParent($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeComponent(Model\ComponentInterface $component)
    {
        if ($this->hasComponent($component)) {
            $this->components->removeElement($component);
            $component->setParent(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setComponents(Collection $components)
    {
        foreach ($this->components as $component) {
            $this->removeComponent($component);
        }

        foreach ($components as $component) {
            $this->addComponent($component);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCrossSellings()
    {
        return $this->crossSellings;
    }

    /**
     * @inheritdoc
     */
    public function hasCrossSellings()
    {
        return 0 < $this->crossSellings->count();
    }

    /**
     * @inheritdoc
     */
    public function hasCrossSelling(Model\CrossSellingInterface $crossSelling)
    {
        return $this->crossSellings->contains($crossSelling);
    }

    /**
     * @inheritdoc
     */
    public function addCrossSelling(Model\CrossSellingInterface $crossSelling)
    {
        if (!$this->hasCrossSelling($crossSelling)) {
            $this->crossSellings->add($crossSelling);
            $crossSelling->setSource($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCrossSelling(Model\CrossSellingInterface $crossSelling)
    {
        if ($this->hasCrossSelling($crossSelling)) {
            $this->crossSellings->removeElement($crossSelling);
            $crossSelling->setSource(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCrossSellings(Collection $crossSellings)
    {
        foreach ($this->crossSellings as $crossSelling) {
            $this->removeCrossSelling($crossSelling);
        }

        foreach ($crossSellings as $crossSelling) {
            $this->addCrossSelling($crossSelling);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSpecialOffers()
    {
        return $this->specialOffers;
    }

    /**
     * @inheritdoc
     */
    public function hasSpecialOffer(Model\SpecialOfferInterface $offer)
    {
        return $this->specialOffers->contains($offer);
    }

    /**
     * @inheritdoc
     */
    public function addSpecialOffer(Model\SpecialOfferInterface $offer)
    {
        if (!$this->hasSpecialOffer($offer)) {
            $this->specialOffers->add($offer);
            $offer->setProduct($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeSpecialOffer(Model\SpecialOfferInterface $offer)
    {
        if ($this->hasSpecialOffer($offer)) {
            $this->specialOffers->removeElement($offer);
            $offer->setProduct(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSpecialOffers(Collection $offers)
    {
        foreach ($this->specialOffers as $offer) {
            $this->removeSpecialOffer($offer);
        }

        foreach ($offers as $offer) {
            $this->addSpecialOffer($offer);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPricings()
    {
        return $this->pricings;
    }

    /**
     * @inheritdoc
     */
    public function hasPricing(Model\PricingInterface $pricing)
    {
        return $this->pricings->contains($pricing);
    }

    /**
     * @inheritdoc
     */
    public function addPricing(Model\PricingInterface $pricing)
    {
        if (!$this->hasPricing($pricing)) {
            $this->pricings->add($pricing);
            $pricing->setProduct($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removePricing(Model\PricingInterface $pricing)
    {
        if ($this->hasPricing($pricing)) {
            $this->pricings->removeElement($pricing);
            $pricing->setProduct(null);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setPricings(Collection $pricings)
    {
        foreach ($this->pricings as $pricing) {
            $this->removePricing($pricing);
        }

        foreach ($pricings as $pricing) {
            $this->addPricing($pricing);
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
    public function setCategories(Collection $categories)
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
    public function setCustomerGroups(Collection $customerGroups)
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

            return $this->medias->filter(function (Model\ProductMediaInterface $media) use ($types) {
                return in_array($media->getMedia()->getType(), $types);
            });
        }

        return $this->medias;
    }

    /**
     * @inheritdoc
     */
    public function isNotContractual(): bool
    {
        return $this->notContractual;
    }

    /**
     * @inheritdoc
     */
    public function setNotContractual(bool $notContractual): Model\ProductInterface
    {
        $this->notContractual = $notContractual;

        return $this;
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
                return $reference->getCode();
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
    public function setType(string $type)
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
    public function setTitle(string $title)
    {
        $this->translate()->setTitle($title);

        return $this;
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
    public function setSubTitle(string $subTitle)
    {
        $this->translate()->setSubTitle($subTitle);

        return $this;
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
    public function setAttributesTitle(string $attributesTitle)
    {
        if ($this->type === Model\ProductTypes::TYPE_VARIANT) {
            $this->translate()->setAttributesTitle($attributesTitle);
        }

        return $this;
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

            return sprintf('%s %s', $this->parent->getFullTitle($withBrand), $title);
        }

        // Prepend the brand
        return $withBrand && $this->brandNaming ? sprintf('%s %s', $this->brand->getTitle(), $title) : $title;
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
    public function setDescription(string $description)
    {
        if ($this->type !== Model\ProductTypes::TYPE_VARIANT) {
            $this->translate()->setDescription($description);
        }

        return $this;
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
    public function setSlug(string $slug)
    {
        $this->translate()->setSlug($slug);

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
    public function isBrandNaming(): bool
    {
        return $this->brandNaming;
    }

    /**
     * @inheritdoc
     */
    public function setBrandNaming(bool $naming): Model\ProductInterface
    {
        $this->brandNaming = $naming;

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

            return sprintf('%s %s', $this->parent->getFullDesignation($withBrand), $designation);
        }

        // Prepend the brand
        return $withBrand && $this->brandNaming
            ? sprintf('%s %s', $this->brand->getName(), $designation)
            : $designation;
    }

    /**
     * @inheritdoc
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    /**
     * @inheritdoc
     */
    public function setMinPrice($minPrice)
    {
        $this->minPrice = $minPrice;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isPendingOffers()
    {
        return $this->pendingOffers;
    }

    /**
     * @inheritdoc
     */
    public function setPendingOffers($pending)
    {
        $this->pendingOffers = (bool)$pending;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isPendingPrices()
    {
        return $this->pendingPrices;
    }

    /**
     * @inheritdoc
     */
    public function setPendingPrices($pending)
    {
        $this->pendingPrices = (bool)$pending;

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
    public function setReleasedAt(\DateTime $date = null)
    {
        $this->releasedAt = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBestSeller()
    {
        return $this->bestSeller;
    }

    /**
     * @inheritdoc
     */
    public function setBestSeller(int $value)
    {
        $this->bestSeller = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCrossSelling()
    {
        return $this->crossSelling;
    }

    /**
     * @inheritdoc
     */
    public function setCrossSelling(int $value)
    {
        $this->crossSelling = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatUpdatedAt()
    {
        return $this->statUpdatedAt;
    }

    /**
     * @inheritdoc
     */
    public function setStatUpdatedAt(\DateTime $date = null)
    {
        $this->statUpdatedAt = $date;

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
     * @inheritDoc
     */
    public function hasMention(ProductMention $mention): bool
    {
        return $this->mentions->contains($mention);
    }

    /**
     * @inheritDoc
     */
    public function addMention(ProductMention $mention): Model\ProductInterface
    {
        if (!$this->hasMention($mention)) {
            $this->mentions->add($mention);
            $mention->setProduct($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeMention(ProductMention $mention): Model\ProductInterface
    {
        if ($this->hasMention($mention)) {
            $this->mentions->removeElement($mention);
            $mention->setProduct(null);
        }

        return $this;
    }

    /**
     * Returns the product's main image.
     *
     * @return Media\MediaInterface|null
     */
    public function getImage(): ?Media\MediaInterface
    {
        $limit = 1;
        $images = $this->gatherMedias(Media\MediaTypes::IMAGE, true, $limit);

        return $images->isEmpty() ? null : $images->first();
    }

    /**
     * @inheritdoc
     */
    public function getImages($withChildren = true, $limit = 5)
    {
        return $this->gatherMedias(Media\MediaTypes::IMAGE, $withChildren, $limit);
    }

    /**
     * @inheritdoc
     */
    public function getFiles($withChildren = false, $limit = 5)
    {
        return $this->gatherMedias(Media\MediaTypes::FILE, $withChildren, $limit);
    }

    /**
     * Gathers medias
     *
     * @param string               $type
     * @param bool                 $recurse
     * @param int                  $limit
     * @param ArrayCollection|null $collection
     *
     * @return ArrayCollection
     */
    private function gatherMedias(
        string $type,
        bool $recurse = true,
        int &$limit = 5,
        ArrayCollection $collection = null
    ) {
        if (null === $collection) {
            $collection = new ArrayCollection();
        }

        foreach ($this->medias as $pm) {
            $media = $pm->getMedia();
            if ($media->getType() === $type && !$collection->contains($media)) {
                $collection->add($media);
                $limit--;
                if (0 >= $limit) {
                    break;
                }
            }
        }

        if ($recurse && $limit) {
            if ($this->type === Model\ProductTypes::TYPE_VARIABLE) {
                /** @var Product $variant TODO */
                foreach ($this->variants as $variant) {
                    $variant->gatherMedias($type, false, $limit, $collection);
                }
            } elseif (in_array($this->type, [Model\ProductTypes::TYPE_BUNDLE, Model\ProductTypes::TYPE_CONFIGURABLE])) {
                foreach ($this->bundleSlots as $slot) {
                    $choices = $slot->getChoices();
                    foreach ($choices as $choice) {
                        if ($type === Media\MediaTypes::IMAGE && $choice->isExcludeImages()) {
                            continue;
                        }

                        $product = $choice->getProduct();
                        if (0 < $product->getMedias()->count()) {
                            foreach ($product->getMedias() as $pm) {
                                $media = $pm->getMedia();
                                if ($media->getType() === $type && !$collection->contains($media)) {
                                    $collection->add($media);
                                    $limit--;
                                    if (0 >= $limit) {
                                        break 3;
                                    }
                                    break;
                                }
                            }
                        } elseif ($product->getType() === Model\ProductTypes::TYPE_VARIABLE) {
                            foreach ($product->getVariants() as $variant) {
                                $variant->gatherMedias($type, false, $limit, $collection);
                            }
                        }
                    }
                }
            }
        }

        return $collection;
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
        $couples = array_map(function ($k, $v) {
            return $k . ':' . $v;
        }, array_keys($values), $values);

        return md5(implode('-', $couples));
    }

    /**
     * @inheritdoc
     */
    protected function getTranslationClass(): string
    {
        return ProductTranslation::class;
    }

    /**
     * @inheritdoc
     */
    public static function getStockUnitClass(): string
    {
        return ProductStockUnit::class;
    }

    /**
     * @inheritDoc
     */
    public function isStockCompound(): bool
    {
        return Model\ProductTypes::isParentType($this->type);
    }

    /**
     * @inheritDoc
     */
    public function getStockComposition(): array
    {
        $composition = [];

        if ($this->type === Model\ProductTypes::TYPE_VARIABLE) {
            // Variants as choices
            $composition[] = array_map(function (Model\ProductInterface $variant) {
                return new Stock\StockComponent($variant, 1); // TODO Deal with units
            }, $this->variants->toArray());
        } elseif ($this->type === Model\ProductTypes::TYPE_BUNDLE) {
            // Slots choice as composition
            $composition = array_map(function (Model\BundleSlotInterface $slot) {
                /** @var Model\BundleChoiceInterface $choice */
                $choice = $slot->getChoices()->first();

                return new Stock\StockComponent($choice->getProduct(), $choice->getMinQuantity());
            }, $this->bundleSlots->toArray());
        } elseif ($this->type === Model\ProductTypes::TYPE_CONFIGURABLE) {
            /** @var Model\BundleSlotInterface $slot */
            foreach ($this->bundleSlots->toArray() as $slot) {
                $composition[] = array_map(function (Model\BundleChoiceInterface $choice) {
                    return new Stock\StockComponent($choice->getProduct(), $choice->getMinQuantity());
                }, $slot->getChoices()->toArray());
            }
        }

        // Components
        foreach ($this->components as $component) {
            $composition[] = new Stock\StockComponent($component->getChild(), $component->getQuantity());
        }

        // Options
        foreach ($this->optionGroups as $group) {
            if (!$group->isRequired()) {
                continue;
            }

            $options = [];
            foreach ($group->getOptions() as $option) {
                if (!$product = $option->getProduct()) {
                    continue;
                }
                $options[] = new Stock\StockComponent($product, 1);
            }

            $composition[] = $options;
        }

        return $composition;
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
    public static function getProviderName(): string
    {
        return ProductProvider::NAME;
    }
}

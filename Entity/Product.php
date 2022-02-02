<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Entity;

use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\CmsBundle\Model as Cms;
use Ekyna\Bundle\MediaBundle\Model as Media;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\ProductMentionInterface;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ProductProvider;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Stock\Model as Stock;
use Ekyna\Component\Resource\Copier\CopierInterface;
use Ekyna\Component\Resource\Model as RM;

use function array_map;
use function intval;
use function sprintf;

/**
 * Class Product
 * @package Ekyna\Bundle\ProductBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method Model\ProductTranslationInterface translate(string $locale = null, bool $create = false)
 * @method Collection<Model\ProductTranslationInterface> getTranslations()
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
    use Stock\StockSubjectTrait {
        __clone as stockSubjectClone;
    }

    protected ?string            $type                  = null;
    protected Decimal            $minPrice;
    protected bool               $notContractual        = false;
    protected ?string            $attributesDesignation = null;
    protected bool               $brandNaming           = true; // Include brand name in designation and title
    protected bool               $pendingOffers         = true; // Schedule offers update at creation
    protected bool               $pendingPrices         = true; // Schedule prices update at creation
    protected ?DateTimeInterface $releasedAt            = null;
    protected string             $bestSeller            = Model\HighlightModes::MODE_AUTO;
    protected string             $crossSelling          = Model\HighlightModes::MODE_AUTO;
    protected ?DateTimeInterface $statUpdatedAt         = null;

    protected ?Model\BrandInterface        $brand        = null;
    protected ?Model\ProductInterface      $parent       = null;
    protected ?Model\AttributeSetInterface $attributeSet = null;

    /** @var Collection<Model\ProductInterface> */
    protected Collection $variants;
    /** @var Collection<Model\ProductAttributeInterface> */
    protected Collection $attributes;
    /** @var Collection<Model\OptionGroupInterface> */
    protected Collection $optionGroups;
    /** @var Collection<Model\BundleSlotInterface> */
    protected Collection $bundleSlots;
    /** @var Collection<Model\ComponentInterface> */
    protected Collection $components;
    /** @var Collection<Model\CrossSellingInterface> */
    protected Collection $crossSellings;
    /** @var Collection<Model\SpecialOfferInterface> */
    protected Collection $specialOffers;
    /** @var Collection<Model\PricingInterface> */
    protected Collection $pricings;
    /** @var Collection<Model\CategoryInterface> */
    protected Collection $categories;
    /** @var Collection<CustomerGroupInterface> */
    protected Collection $customerGroups;
    /** @var Collection<Model\ProductMediaInterface> */
    protected Collection $medias;
    /** @var Collection<Model\ProductReferenceInterface> */
    protected Collection $references;

    public function __construct()
    {
        parent::__construct();

        $this->minPrice = new Decimal(0);

        $this->attributes = new ArrayCollection();
        $this->bundleSlots = new ArrayCollection();
        $this->components = new ArrayCollection();
        $this->crossSellings = new ArrayCollection();
        $this->specialOffers = new ArrayCollection();
        $this->pricings = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->medias = new ArrayCollection();
        $this->optionGroups = new ArrayCollection();
        $this->customerGroups = new ArrayCollection();
        $this->references = new ArrayCollection();
        $this->variants = new ArrayCollection();

        $this->initializeTags();
        $this->initializeAdjustments();
        $this->initializeMentions();
        $this->initializeStock();
    }

    public function __clone()
    {
        parent::__clone();

        $this->stockSubjectClone();
    }

    public function onCopy(CopierInterface $copier): void
    {
        parent::onCopy($copier);

        // ---- ONE TO MANY ----

        $copier->copyCollection($this, 'adjustments', true);
        $copier->copyCollection($this, 'attributes', true);
        $copier->copyCollection($this, 'bundleSlots', true);
        $copier->copyCollection($this, 'components', true);
        $copier->copyCollection($this, 'medias', true);
        $copier->copyCollection($this, 'optionGroups', true);
        $copier->copyCollection($this, 'variants', true);
        $copier->copyCollection($this, 'crossSellings', true);
        $copier->copyCollection($this, 'specialOffers', true);
        $copier->copyCollection($this, 'pricings', true);
        $copier->copyCollection($this, 'mentions', true);

        $this->references = new ArrayCollection();

        // ---- MANY TO MANY ----

        $copier->copyCollection($this, 'categories', false);
        $copier->copyCollection($this, 'customerGroups', false);
        $copier->copyCollection($this, 'tags', false);

        // ---- MANY TO ONE ----

        // Brand is ok
        // Parent is ok
        // Tax group is ok
        // Attribute set is ok

        // ---- ONE TO ONE ----

        $this->content = null;
        if ($this->seo) {
            $this->seo = $copier->copyResource($this->seo);
        }

        // ---- BASICS ----

        // Reset stock data (but preserve mode and quoteOnly)
        $stockMode = $this->stockMode;
        $quoteOnly = $this->quoteOnly;
        $this->initializeStock();
        $this->stockMode = $stockMode;
        $this->quoteOnly = $quoteOnly;

        // Clear critical fields
        $this->designation = null;
        $this->reference = null;
        $this->geocode = null;
        $this->visible = false;
        $this->notContractual = true;
        $this->minPrice = new Decimal(0);
        //$this->releasedAt = null;
    }

    public function __toString(): string
    {
        return $this->getFullDesignation(true) ?: 'New product';
    }

    public function getIdentifier(): int
    {
        return $this->getId();
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): Model\ProductInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getMinPrice(): Decimal
    {
        return $this->minPrice;
    }

    public function setMinPrice(Decimal $minPrice): Model\ProductInterface
    {
        $this->minPrice = $minPrice;

        return $this;
    }

    public function isNotContractual(): bool
    {
        return $this->notContractual;
    }

    public function setNotContractual(bool $notContractual): Model\ProductInterface
    {
        $this->notContractual = $notContractual;

        return $this;
    }

    public function getAttributesDesignation(): ?string
    {
        return $this->attributesDesignation;
    }

    public function setAttributesDesignation(?string $attributesDesignation): Model\ProductInterface
    {
        $this->attributesDesignation = $attributesDesignation;

        return $this;
    }

    public function isBrandNaming(): bool
    {
        return $this->brandNaming;
    }

    public function setBrandNaming(bool $naming): Model\ProductInterface
    {
        $this->brandNaming = $naming;

        return $this;
    }

    public function isPendingOffers(): bool
    {
        return $this->pendingOffers;
    }

    public function setPendingOffers(bool $pending): Model\ProductInterface
    {
        $this->pendingOffers = $pending;

        return $this;
    }

    public function isPendingPrices(): bool
    {
        return $this->pendingPrices;
    }

    public function setPendingPrices(bool $pending): Model\ProductInterface
    {
        $this->pendingPrices = $pending;

        return $this;
    }

    public function getReleasedAt(): ?DateTimeInterface
    {
        return $this->releasedAt;
    }

    public function setReleasedAt(?DateTimeInterface $date): Model\ProductInterface
    {
        $this->releasedAt = $date;

        return $this;
    }

    public function getBestSeller(): string
    {
        return $this->bestSeller;
    }

    public function setBestSeller(string $mode): Model\ProductInterface
    {
        $this->bestSeller = $mode;

        return $this;
    }

    public function getCrossSelling(): string
    {
        return $this->crossSelling;
    }

    public function setCrossSelling(string $mode): Model\ProductInterface
    {
        $this->crossSelling = $mode;

        return $this;
    }

    public function getStatUpdatedAt(): ?DateTimeInterface
    {
        return $this->statUpdatedAt;
    }

    public function setStatUpdatedAt(?DateTimeInterface $date): Model\ProductInterface
    {
        $this->statUpdatedAt = $date;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->translate()->getTitle();
    }

    public function setTitle(?string $title): Model\ProductInterface
    {
        $this->translate()->setTitle($title);

        return $this;
    }

    public function getSubTitle(): ?string
    {
        return $this->translate()->getSubTitle();
    }

    public function setSubTitle(?string $subTitle): Model\ProductInterface
    {
        $this->translate()->setSubTitle($subTitle);

        return $this;
    }

    public function getAttributesTitle(): ?string
    {
        return $this->translate()->getAttributesTitle();
    }

    public function setAttributesTitle(?string $attributesTitle): Model\ProductInterface
    {
        if ($this->type === Model\ProductTypes::TYPE_VARIANT) {
            $this->translate()->setAttributesTitle($attributesTitle);
        }

        return $this;
    }

    public function getFullTitle(bool $withBrand = false): ?string
    {
        $title = $this->getTitle();

        // Variant : parent title + variant title
        if ($this->type === Model\ProductTypes::TYPE_VARIANT) {
            if (empty($title)) {
                // Fallback to auto-generated title
                $title = $this->getAttributesTitle();
            }

            return sprintf('%s %s', $this->parent->getFullTitle($withBrand), $title);
        }

        if ($withBrand && $this->brandNaming && $this->brand) {
            return sprintf('%s %s', $this->brand->getTitle(), $title);
        }

        return $title;
    }

    public function getDescription(): ?string
    {
        // Variant : fallback to parent description
        if ($this->type === Model\ProductTypes::TYPE_VARIANT) {
            return $this->parent->getDescription();
        }

        return $this->translate()->getDescription();
    }

    public function setDescription(?string $description): Model\ProductInterface
    {
        if ($this->type !== Model\ProductTypes::TYPE_VARIANT) {
            $this->translate()->setDescription($description);
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->translate()->getSlug();
    }

    public function setSlug(?string $slug): Model\ProductInterface
    {
        $this->translate()->setSlug($slug);

        return $this;
    }

    public function getFullDesignation(bool $withBrand = false): ?string
    {
        $designation = $this->getDesignation();

        // Variant : parent designation + variant designation
        if ($this->type === Model\ProductTypes::TYPE_VARIANT) {
            if (empty($designation)) {
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

    public function getBrand(): ?Model\BrandInterface
    {
        return $this->brand;
    }

    public function setBrand(Model\BrandInterface $brand): Model\ProductInterface
    {
        $this->brand = $brand;

        return $this;
    }

    public function getParent(): ?Model\ProductInterface
    {
        return $this->parent;
    }

    public function setParent(?Model\ProductInterface $parent): Model\ProductInterface
    {
        if ($this->parent === $parent) {
            return $this;
        }

        if ($previous = $this->parent) {
            $this->parent = null;
            $previous->removeVariant($this);
        }

        if ($this->parent = $parent) {
            $this->parent->addVariant($this);
        }

        return $this;
    }

    public function getVariants(): Collection
    {
        return $this->variants;
    }

    public function hasVariant(Model\ProductInterface $variant): bool
    {
        return $this->variants->contains($variant);
    }

    public function addVariant(Model\ProductInterface $variant): Model\ProductInterface
    {
        if (!$this->hasVariant($variant)) {
            $this->variants->add($variant);
            $variant->setParent($this);
        }

        return $this;
    }

    public function removeVariant(Model\ProductInterface $variant): Model\ProductInterface
    {
        if ($this->hasVariant($variant)) {
            $this->variants->removeElement($variant);
            $variant->setParent(null);
        }

        return $this;
    }

    public function getAttributeSet(): ?Model\AttributeSetInterface
    {
        return $this->attributeSet;
    }

    public function setAttributeSet(?Model\AttributeSetInterface $attributeSet): Model\ProductInterface
    {
        $this->attributeSet = $attributeSet;

        return $this;
    }

    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function hasAttribute(Model\ProductAttributeInterface $attribute): bool
    {
        return $this->attributes->contains($attribute);
    }

    public function addAttribute(Model\ProductAttributeInterface $attribute): Model\ProductInterface
    {
        if (!$this->hasAttribute($attribute)) {
            $this->attributes->add($attribute);
            $attribute->setProduct($this);
        }

        return $this;
    }

    public function removeAttribute(Model\ProductAttributeInterface $attribute): Model\ProductInterface
    {
        if ($this->hasAttribute($attribute)) {
            $this->attributes->removeElement($attribute);
            $attribute->setProduct(null);
        }

        return $this;
    }

    public function getOptionGroups(): Collection
    {
        return $this->optionGroups;
    }

    public function hasOptionGroups(): bool
    {
        return 0 < $this->optionGroups->count();
    }

    public function hasOptionGroup(Model\OptionGroupInterface $group): bool
    {
        return $this->optionGroups->contains($group);
    }

    public function addOptionGroup(Model\OptionGroupInterface $group): Model\ProductInterface
    {
        if (!$this->hasOptionGroup($group)) {
            $this->optionGroups->add($group);
            $group->setProduct($this);
        }

        return $this;
    }

    public function removeOptionGroup(Model\OptionGroupInterface $group): Model\ProductInterface
    {
        if ($this->hasOptionGroup($group)) {
            $this->optionGroups->removeElement($group);
            $group->setProduct(null);
        }

        return $this;
    }

    public function setOptionGroups(Collection $groups): Model\ProductInterface
    {
        foreach ($this->optionGroups as $group) {
            $this->removeOptionGroup($group);
        }

        foreach ($groups as $group) {
            $this->addOptionGroup($group);
        }

        return $this;
    }

    public function hasRequiredOptionGroup(array $exclude = []): bool
    {
        $exclude = array_map(fn($id) => intval($id), $exclude);

        // All types
        foreach ($this->optionGroups as $optionGroup) {
            if (in_array($optionGroup->getId(), $exclude, true)) {
                continue;
            }

            if ($optionGroup->isRequired()) {
                return true;
            }
        }

        // A variant inherits options from his parent
        if ($this->parent) {
            foreach ($this->parent->getOptionGroups() as $optionGroup) {
                if (in_array($optionGroup->getId(), $exclude, true)) {
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

        $exclude = array_map(fn($id) => intval($id), $exclude);

        $groups = [];

        if (Model\ProductTypes::isVariantType($this)) {
            foreach ($this->parent->getOptionGroups() as $group) {
                if (in_array($group->getId(), $exclude, true)) {
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
                    if (in_array($group->getId(), $exclude, true)) {
                        continue;
                    }

                    $groups[] = $group;
                }
            }
        }

        foreach ($this->getOptionGroups() as $group) {
            if (in_array($group->getId(), $exclude, true)) {
                continue;
            }

            $groups[] = $group;
        }

        return $groups;
    }

    public function getBundleSlots(): Collection
    {
        return $this->bundleSlots;
    }

    public function hasBundleSlot(Model\BundleSlotInterface $slot): bool
    {
        return $this->bundleSlots->contains($slot);
    }

    public function addBundleSlot(Model\BundleSlotInterface $slot): Model\ProductInterface
    {
        if (!$this->hasBundleSlot($slot)) {
            $this->bundleSlots->add($slot);
            $slot->setBundle($this);
        }

        return $this;
    }

    public function removeBundleSlot(Model\BundleSlotInterface $slot): Model\ProductInterface
    {
        if ($this->hasBundleSlot($slot)) {
            $this->bundleSlots->removeElement($slot);
            $slot->setBundle(null);
        }

        return $this;
    }

    public function setBundleSlots(Collection $slots): Model\ProductInterface
    {
        foreach ($this->bundleSlots as $slot) {
            $this->removeBundleSlot($slot);
        }

        foreach ($slots as $slot) {
            $this->addBundleSlot($slot);
        }

        return $this;
    }

    public function getComponents(): Collection
    {
        return $this->components;
    }

    public function hasComponents(): bool
    {
        return 0 < $this->components->count();
    }

    public function hasComponent(Model\ComponentInterface $component): bool
    {
        return $this->components->contains($component);
    }

    public function addComponent(Model\ComponentInterface $component): Model\ProductInterface
    {
        if (!$this->hasComponent($component)) {
            $this->components->add($component);
            $component->setParent($this);
        }

        return $this;
    }

    public function removeComponent(Model\ComponentInterface $component): Model\ProductInterface
    {
        if ($this->hasComponent($component)) {
            $this->components->removeElement($component);
            $component->setParent(null);
        }

        return $this;
    }

    public function setComponents(Collection $components): Model\ProductInterface
    {
        foreach ($this->components as $component) {
            $this->removeComponent($component);
        }

        foreach ($components as $component) {
            $this->addComponent($component);
        }

        return $this;
    }

    public function getCrossSellings(): Collection
    {
        return $this->crossSellings;
    }

    public function hasCrossSellings(): bool
    {
        return 0 < $this->crossSellings->count();
    }

    public function hasCrossSelling(Model\CrossSellingInterface $crossSelling): bool
    {
        return $this->crossSellings->contains($crossSelling);
    }

    public function addCrossSelling(Model\CrossSellingInterface $crossSelling): Model\ProductInterface
    {
        if (!$this->hasCrossSelling($crossSelling)) {
            $this->crossSellings->add($crossSelling);
            $crossSelling->setSource($this);
        }

        return $this;
    }

    public function removeCrossSelling(Model\CrossSellingInterface $crossSelling): Model\ProductInterface
    {
        if ($this->hasCrossSelling($crossSelling)) {
            $this->crossSellings->removeElement($crossSelling);
            $crossSelling->setSource(null);
        }

        return $this;
    }

    public function setCrossSellings(Collection $crossSellings): Model\ProductInterface
    {
        foreach ($this->crossSellings as $crossSelling) {
            $this->removeCrossSelling($crossSelling);
        }

        foreach ($crossSellings as $crossSelling) {
            $this->addCrossSelling($crossSelling);
        }

        return $this;
    }

    public function getSpecialOffers(): Collection
    {
        return $this->specialOffers;
    }

    public function hasSpecialOffer(Model\SpecialOfferInterface $offer): bool
    {
        return $this->specialOffers->contains($offer);
    }

    public function addSpecialOffer(Model\SpecialOfferInterface $offer): Model\ProductInterface
    {
        if (!$this->hasSpecialOffer($offer)) {
            $this->specialOffers->add($offer);
            $offer->setProduct($this);
        }

        return $this;
    }

    public function removeSpecialOffer(Model\SpecialOfferInterface $offer): Model\ProductInterface
    {
        if ($this->hasSpecialOffer($offer)) {
            $this->specialOffers->removeElement($offer);
            $offer->setProduct(null);
        }

        return $this;
    }

    public function setSpecialOffers(Collection $offers): Model\ProductInterface
    {
        foreach ($this->specialOffers as $offer) {
            $this->removeSpecialOffer($offer);
        }

        foreach ($offers as $offer) {
            $this->addSpecialOffer($offer);
        }

        return $this;
    }

    public function getPricings(): Collection
    {
        return $this->pricings;
    }

    public function hasPricing(Model\PricingInterface $pricing): bool
    {
        return $this->pricings->contains($pricing);
    }

    public function addPricing(Model\PricingInterface $pricing): Model\ProductInterface
    {
        if (!$this->hasPricing($pricing)) {
            $this->pricings->add($pricing);
            $pricing->setProduct($this);
        }

        return $this;
    }

    public function removePricing(Model\PricingInterface $pricing): Model\ProductInterface
    {
        if ($this->hasPricing($pricing)) {
            $this->pricings->removeElement($pricing);
            $pricing->setProduct(null);
        }

        return $this;
    }

    public function setPricings(Collection $pricings): Model\ProductInterface
    {
        foreach ($this->pricings as $pricing) {
            $this->removePricing($pricing);
        }

        foreach ($pricings as $pricing) {
            $this->addPricing($pricing);
        }

        return $this;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function hasCategory(Model\CategoryInterface $category): bool
    {
        return $this->categories->contains($category);
    }

    public function addCategory(Model\CategoryInterface $category): Model\ProductInterface
    {
        if (!$this->hasCategory($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Model\CategoryInterface $category): Model\ProductInterface
    {
        if ($this->hasCategory($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }

    public function setCategories(Collection $categories): Model\ProductInterface
    {
        foreach ($this->categories as $category) {
            $this->removeCategory($category);
        }

        foreach ($categories as $category) {
            $this->addCategory($category);
        }

        return $this;
    }

    public function getCustomerGroups(): Collection
    {
        return $this->customerGroups;
    }

    public function hasCustomerGroup(CustomerGroupInterface $group): bool
    {
        return $this->customerGroups->contains($group);
    }

    public function addCustomerGroup(CustomerGroupInterface $group): Model\ProductInterface
    {
        if (!$this->hasCustomerGroup($group)) {
            $this->customerGroups->add($group);
        }

        return $this;
    }

    public function removeCustomerGroup(CustomerGroupInterface $group): Model\ProductInterface
    {
        if ($this->hasCustomerGroup($group)) {
            $this->customerGroups->removeElement($group);
        }

        return $this;
    }

    public function setCustomerGroups(Collection $groups): Model\ProductInterface
    {
        foreach ($this->customerGroups as $group) {
            $this->removeCustomerGroup($group);
        }

        foreach ($groups as $group) {
            $this->addCustomerGroup($group);
        }

        return $this;
    }

    public function hasMedia(Model\ProductMediaInterface $media): bool
    {
        return $this->medias->contains($media);
    }

    public function addMedia(Model\ProductMediaInterface $media): Model\ProductInterface
    {
        if (!$this->hasMedia($media)) {
            $this->medias->add($media);
            $media->setProduct($this);
            // TODO ??? $this->setUpdatedAt(new \DateTime());
        }

        return $this;
    }

    public function removeMedia(Model\ProductMediaInterface $media): Model\ProductInterface
    {
        if ($this->hasMedia($media)) {
            $this->medias->removeElement($media);
            $media->setProduct(null);
            // TODO ??? $this->setUpdatedAt(new \DateTime());
        }

        return $this;
    }

    public function getMedias(array $types = []): Collection
    {
        if (!empty($types)) {
            foreach ($types as $type) {
                Media\MediaTypes::isValid($type, true);
            }

            return $this->medias->filter(function (Model\ProductMediaInterface $media) use ($types) {
                return in_array($media->getMedia()->getType(), $types, true);
            });
        }

        return $this->medias;
    }

    public function hasReference(Model\ProductReferenceInterface $reference): bool
    {
        return $this->references->contains($reference);
    }

    public function addReference(Model\ProductReferenceInterface $reference): Model\ProductInterface
    {
        if (!$this->hasReference($reference)) {
            $this->references->add($reference);
            $reference->setProduct($this);
        }

        return $this;
    }

    public function removeReference(Model\ProductReferenceInterface $reference): Model\ProductInterface
    {
        if ($this->hasReference($reference)) {
            $this->references->removeElement($reference);
            $reference->setProduct(null);
        }

        return $this;
    }

    public function getReferences(): Collection
    {
        return $this->references;
    }

    public function getReferenceByType(string $type): ?string
    {
        Model\ProductReferenceTypes::isValid($type, true);

        foreach ($this->references as $reference) {
            if ($reference->getType() === $type) {
                return $reference->getCode();
            }
        }

        return null;
    }

    public function hasAdjustment(Common\AdjustmentInterface $adjustment): bool
    {
        if (!$adjustment instanceof Model\ProductAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\ProductAdjustmentInterface::class);
        }

        return $this->adjustments->contains($adjustment);
    }

    public function addAdjustment(Common\AdjustmentInterface $adjustment): Common\AdjustableInterface
    {
        if (!$adjustment instanceof Model\ProductAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\ProductAdjustmentInterface::class);
        }

        if (!$this->hasAdjustment($adjustment)) {
            $this->adjustments->add($adjustment);
            $adjustment->setProduct($this);
        }

        return $this;
    }

    public function removeAdjustment(Common\AdjustmentInterface $adjustment): Common\AdjustableInterface
    {
        if (!$adjustment instanceof Model\ProductAdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, Model\ProductAdjustmentInterface::class);
        }

        if ($this->hasAdjustment($adjustment)) {
            $this->adjustments->removeElement($adjustment);
            $adjustment->setProduct(null);
        }

        return $this;
    }

    public function hasMention(ProductMentionInterface $mention): bool
    {
        return $this->mentions->contains($mention);
    }

    public function addMention(ProductMentionInterface $mention): Model\ProductInterface
    {
        if (!$this->hasMention($mention)) {
            $this->mentions->add($mention);
            $mention->setProduct($this);
        }

        return $this;
    }

    public function removeMention(ProductMentionInterface $mention): Model\ProductInterface
    {
        if ($this->hasMention($mention)) {
            $this->mentions->removeElement($mention);
            $mention->setProduct(null);
        }

        return $this;
    }

    public function getImage(): ?Media\MediaInterface
    {
        $limit = 1;
        $images = $this->gatherMedias(Media\MediaTypes::IMAGE, true, $limit);

        return $images->isEmpty() ? null : $images->first();
    }

    public function getImages(bool $withChildren = true, int $limit = 5): Collection
    {
        return $this->gatherMedias(Media\MediaTypes::IMAGE, $withChildren, $limit);
    }

    public function getFiles(bool $withChildren = false, int $limit = 5): Collection
    {
        return $this->gatherMedias(Media\MediaTypes::FILE, $withChildren, $limit);
    }

    private function gatherMedias(
        string          $type,
        bool            $recurse = true,
        int             &$limit = 5,
        ArrayCollection $collection = null
    ): Collection {
        if (null === $collection) {
            $collection = new ArrayCollection();
        }

        // TODO "Add media to collection" closure

        foreach ($this->medias as $pm) {
            $media = $pm->getMedia();
            if ($media->getType() === $type && !$collection->contains($media)) {
                $collection->add($media);
                $limit--;
                if (0 >= $limit) {
                    return $collection;
                }
            }
        }

        // TODO If product is variant, add medias from parent

        if ($recurse && $limit) {
            if ($this->type === Model\ProductTypes::TYPE_VARIABLE) {
                /** @var Product $variant TODO */
                foreach ($this->variants as $variant) {
                    $variant->gatherMedias($type, false, $limit, $collection);
                }
            } elseif (in_array($this->type, [
                Model\ProductTypes::TYPE_BUNDLE,
                Model\ProductTypes::TYPE_CONFIGURABLE,
            ], true)) {
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

    public function getUniquenessSignature(): string
    {
        Model\ProductTypes::assertVariant($this);

        $values = [];
        foreach ($this->attributes as $pr) {
            $key = $pr->getAttributeSlot()->getAttribute()->getId();
            if (!empty($value = $pr->getValue())) {
                $values[$key] = $value;
            } else {
                $ids = [];
                /** @var Model\AttributeChoiceInterface $choice */
                foreach ($pr->getChoices()->toArray() as $choice) {
                    $ids[] = $choice->getId();
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

    protected function getTranslationClass(): string
    {
        return ProductTranslation::class;
    }

    public static function getStockUnitClass(): string
    {
        return ProductStockUnit::class;
    }

    public function isStockCompound(): bool
    {
        return Model\ProductTypes::isParentType($this->type);
    }

    public function getStockComposition(): array
    {
        $composition = [];

        if ($this->type === Model\ProductTypes::TYPE_VARIABLE) {
            // Variants as choices
            $composition[] = array_map(function (Model\ProductInterface $variant) {
                return new Stock\StockComponent($variant, new Decimal(1)); // TODO Deal with units
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
            $composition[] = new Stock\StockComponent($component->getChild(), clone $component->getQuantity());
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
                $options[] = new Stock\StockComponent($product, new Decimal(1));
            }

            $composition[] = $options;
        }

        return $composition;
    }

    public static function getEntityTagPrefix(): string
    {
        return 'ekyna_product.product';
    }

    public static function getProviderName(): string
    {
        return ProductProvider::NAME;
    }
}

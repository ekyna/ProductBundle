<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface;
use Ekyna\Bundle\ProductBundle\Model\ComponentInterface;
use Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface;
use Ekyna\Bundle\ProductBundle\Model\OptionInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception;

use function array_merge;
use function array_unique;

/**
 * Class ItemBuilder
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ItemBuilder
{
    public const VARIANT_ID       = 'variant_id';
    public const BUNDLE_SLOT_ID   = 'bundle_slot_id';
    public const BUNDLE_CHOICE_ID = 'bundle_choice_id';
    public const OPTION_GROUP_ID  = 'option_group_id';
    public const OPTION_ID        = 'option_id';
    public const COMPONENT_ID     = 'component_id';

    protected ProductProvider        $provider;
    protected ProductFilterInterface $filter;

    public function __construct(ProductProvider $provider, ProductFilterInterface $filter)
    {
        $this->provider = $provider;
        $this->filter = $filter;
    }

    /**
     * Returns the product provider.
     *
     * @return ProductProvider
     */
    public function getProvider(): ProductProvider
    {
        return $this->provider;
    }

    /**
     * Returns the product filter.
     *
     * @return ProductFilterInterface
     */
    public function getFilter(): ProductFilterInterface
    {
        return $this->filter;
    }

    /**
     * Builds the sale item (subject must be assigned).
     *
     * @param SaleItemInterface $item
     */
    public function build(SaleItemInterface $item): void
    {
        $product = $this->resolve($item);

        $this->buildFromProduct($item, $product, []);
    }

    /**
     * Builds the sale item from the given product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array|null        $exclude The option group ids to exclude
     *
     * @throws Exception\InvalidArgumentException If product type is not supported
     */
    protected function buildFromProduct(SaleItemInterface $item, ProductInterface $product, array $exclude = null): void
    {
        switch ($product->getType()) {
            case ProductTypes::TYPE_SIMPLE:
                $this->buildFromSimple($item, $product);
                break;
            case ProductTypes::TYPE_VARIANT:
                $this->buildFromVariant($item, $product);
                break;
            case ProductTypes::TYPE_VARIABLE:
                $this->buildFromVariable($item, $product);
                break;
            case ProductTypes::TYPE_BUNDLE:
                $this->buildFromBundle($item, $product, $exclude);
                break;
            case ProductTypes::TYPE_CONFIGURABLE:
                $this->buildFromConfigurable($item, $product, $exclude);
                break;
            default:
                throw new Exception\InvalidArgumentException('Unexpected product type');
        }

        if (!is_null($exclude)) {
            $this->buildOptions($item, $exclude);
        }

        $this->buildComponents($item);
    }

    /**
     * Builds the sale item from the simple product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    protected function buildFromSimple(SaleItemInterface $item, ProductInterface $product): void
    {
        ProductTypes::assertChildType($product);

        $this->provider->assign($item, $product);

        $item->setDesignation($product->getFullTitle(true));
        $item->setReference($product->getReference());
        $item->setNetPrice(clone $product->getNetPrice());
        $item->setWeight(clone $product->getPackageWeight());
        $item->setTaxGroup($product->getTaxGroup());
        $item->setCompound(false);
        $item->setConfigurable(false);
        $item->setPrivate(!$product->isVisible());

        $this->cleanUpBundleSlots($item);
    }

    /**
     * Builds the sale item from the given variable product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     *
     * @throws Exception\LogicException If the product has no variant
     */
    protected function buildFromVariable(SaleItemInterface $item, ProductInterface $product): void
    {
        ProductTypes::assertVariable($product);

        $variant = null;
        $variants = $this->filter->getVariants($product);

        if (empty($variants)) {
            throw new Exception\LogicException('Variable product must have at least one variant.');
        }

        if (0 < ($variantId = intval($item->getDatum(self::VARIANT_ID)))) {
            foreach ($variants as $v) {
                if ($variantId == $v->getId()) {
                    $variant = $v;
                    break;
                }
            }
        }

        if (null === $variant) {
            $variant = reset($variants);
        }

        $this->buildFromVariant($item, $variant);
        // TODO Variable product should always be public (not private) as it is a user choice.
    }

    /**
     * Builds the sale item from the given variant product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $variant
     */
    public function buildFromVariant(SaleItemInterface $item, ProductInterface $variant): void
    {
        ProductTypes::assertVariant($variant);

        $this->buildFromSimple($item, $variant);

        $item->setDatum(self::VARIANT_ID, $variant->getId()); // TODO Useless
    }

    /**
     * Builds the sale item from the given bundle product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array|null        $exclude The option groups ids to exclude
     */
    protected function buildFromBundle(SaleItemInterface $item, ProductInterface $product, array $exclude = null): void
    {
        ProductTypes::assertBundle($product);

        $this->provider->assign($item, $product);

        // Bundle root item
        $item->setDesignation($product->getFullTitle(true));
        $item->setReference($product->getReference());
        $item->setNetPrice(new Decimal(0));
        $item->setTaxGroup($product->getTaxGroup());
        $item->setCompound(true);
        $item->setConfigurable(false);
        $item->setPrivate(!$product->isVisible());

        // (Do not filter bundle slots)
        /** @var BundleSlotInterface[] $bundlesSlots */
        $bundlesSlots = $product->getBundleSlots()->toArray();

        // Every slot must match a single item
        $bundleSlotIds = [];
        foreach ($bundlesSlots as $bundleSlot) {
            $choices = $this->filter->getSlotChoices($bundleSlot);
            if (empty($choices)) {
                continue;
            }

            $bundleChoice = current($choices);

            // Find matching item
            foreach ($item->getChildren() as $childItem) {
                $bundleSlotId = intval($childItem->getDatum(self::BUNDLE_SLOT_ID));
                if ($bundleSlotId != $bundleSlot->getId()) {
                    continue;
                }

                // Remove bundle slot duplicates
                if (in_array($bundleSlotId, $bundleSlotIds)) {
                    $item->removeChild($childItem);
                    continue;
                }
                $bundleSlotIds[] = $bundleSlotId;

                // Build the item from the bundle choice's product
                $this->buildFromBundleChoice($childItem, $bundleChoice, $exclude);

                continue 2;
            }

            $bundleSlotIds[] = $bundleSlot->getId();

            // Not found : Create and build the item from the bundle choice's product
            $this->buildFromBundleChoice($item->createChild(), $bundleChoice, $exclude);
        }

        $this->cleanUpBundleSlots($item, $bundleSlotIds);

        if ($product->getWeight()->isZero()) {
            return;
        }

        // Weight override case
        $item->setWeight(clone $product->getWeight());
        $this->clearChildrenWeight($item);
    }

    private function clearChildrenWeight(SaleItemInterface $item): void
    {
        foreach ($item->getChildren() as $child) {
            $child->setWeight(new Decimal(0));

            $this->clearChildrenWeight($child);
        }
    }

    /**
     * Builds the sale item from the given configurable product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array|null        $exclude
     */
    protected function buildFromConfigurable(
        SaleItemInterface $item,
        ProductInterface  $product,
        array             $exclude = null
    ): void {
        ProductTypes::assertConfigurable($product);

        $this->provider->assign($item, $product);

        // Configurable root item
        $item->setDesignation($product->getFullTitle(true));
        $item->setReference($product->getReference());
        $item->setNetPrice(new Decimal(0));
        $item->setTaxGroup($product->getTaxGroup());
        $item->setCompound(true);
        $item->setConfigurable(true);
        $item->setPrivate(false);

        // Filter bundle slots
        $bundlesSlots = $this->filter->getBundleSlots($product);

        // Every slot must match a single item
        $bundleSlotIds = [];
        foreach ($bundlesSlots as $bundleSlot) {
            $choices = $this->filter->getSlotChoices($bundleSlot);
            if (empty($choices)) {
                continue;
            }

            $bundleChoice = current($choices);

            // Find matching item
            foreach ($item->getChildren() as $childItem) {
                $bundleSlotId = intval($childItem->getDatum(self::BUNDLE_SLOT_ID));
                if ($bundleSlotId != $bundleSlot->getId()) {
                    continue;
                }

                // Remove bundle slot duplicates
                if (in_array($bundleSlotId, $bundleSlotIds)) {
                    $item->removeChild($childItem);
                    continue;
                }
                $bundleSlotIds[] = $bundleSlotId;

                // Resolve choice
                if (0 < $bundleChoiceId = intval($childItem->getDatum(self::BUNDLE_CHOICE_ID))) {
                    foreach ($choices as $choice) {
                        if ($bundleChoiceId === $choice->getId()) {
                            $bundleChoice = $choice;
                            break;
                        }
                    }

                    // Build the item from the bundle choice's product
                    $this->buildFromBundleChoice($childItem, $bundleChoice, $exclude);
                } elseif (!$bundleSlot->isRequired()) {
                    // No choice and not required : remove child item
                    $item->removeChild($childItem);
                }

                continue 2;
            }

            $bundleSlotIds[] = $bundleSlot->getId();

            // Not found
            if ($bundleSlot->isRequired()) {
                // Create and build the item from the bundle choice's product
                $this->buildFromBundleChoice($item->createChild(), $bundleChoice, $exclude);
            }
        }

        $this->cleanUpBundleSlots($item, $bundleSlotIds);

        // Direct children must be public as they are user choices.
        foreach ($item->getChildren() as $child) {
            if ($child->hasDatum(self::BUNDLE_SLOT_ID)) {
                $child->setPrivate(false);
            }
        }
    }

    /**
     * Builds the sale item from the given bundle choice.
     *
     * @param SaleItemInterface     $item    The sale item
     * @param BundleChoiceInterface $choice  The bundle choice
     * @param array|null            $exclude The option group ids to exclude
     */
    protected function buildFromBundleChoice(
        SaleItemInterface     $item,
        BundleChoiceInterface $choice,
        array                 $exclude = null
    ): void {
        if (!empty($exclude)) {
            $exclude = array_unique(array_merge($exclude, $choice->getExcludedOptionGroups()));
        } else {
            $exclude = $choice->getExcludedOptionGroups();
        }

        $this->buildFromProduct($item, $choice->getProduct(), $exclude);

        // TODO Use packaging format

        // Normalize quantity
        if ($item->getQuantity() < $choice->getMinQuantity()) {
            $item->setQuantity($choice->getMinQuantity());
        } elseif ($item->getQuantity() > $choice->getMaxQuantity()) {
            $item->setQuantity($choice->getMaxQuantity());
        }

        if ($choice->isHidden()) {
            $item->setPrivate(true);
        }

        // Override item net price (from product) with choice's net price if set
        if ($price = $choice->getNetPrice()) {
            $item->setNetPrice(clone $price);
        }

        $item
            ->setDatum(self::BUNDLE_SLOT_ID, $choice->getSlot()->getId())
            ->setDatum(self::BUNDLE_CHOICE_ID, $choice->getId())
            ->setImmutable(true);
    }

    /**
     * Builds the item's options.
     *
     * @param SaleItemInterface $item
     * @param array             $exclude The option groups ids to exclude
     *
     * @throws Exception\LogicException If an option group is required but no option is selected
     */
    protected function buildOptions(SaleItemInterface $item, array $exclude): void
    {
        $optionGroups = $this->getOptionGroups($item, $exclude);

        if (empty($optionGroups)) {
            $this->cleanUpOptionGroups($item);

            return;
        }

        // Configurable only if root item
        if ($root = $item->getParent()) {
            $root->setConfigurable(true);
        } else {
            $item->setConfigurable(true);
        }

        $optionGroupIds = [];
        foreach ($optionGroups as $optionGroup) {
            // Skip if group has no options
            $options = $this->filter->getGroupOptions($optionGroup);
            if (empty($options)) {
                continue;
            }

            // Find option group matching item
            if ($item->hasChildren()) {
                foreach ($item->getChildren() as $child) {
                    // Check option group id
                    $optionGroupId = intval($child->getDatum(self::OPTION_GROUP_ID));
                    if ($optionGroupId != $optionGroup->getId()) {
                        continue;
                    }

                    // Remove option group duplicates
                    if (in_array($optionGroupId, $optionGroupIds)) {
                        $item->removeChild($child);
                        continue;
                    }
                    $optionGroupIds[] = $optionGroupId;

                    $child
                        ->setQuantity(new Decimal(1))
                        ->setPosition($optionGroup->getPosition());

                    // Check option choice
                    $found = false;
                    if (0 < $optionId = intval($child->getDatum(self::OPTION_ID))) {
                        foreach ($options as $option) {
                            if ($optionId === $option->getId()) {
                                $found = true;

                                $this->buildFromOption($child, $option, count($options));

                                break;
                            }
                        }
                        // Not Found => unset choice
                        if (!$found) {
                            $child->unsetDatum(self::OPTION_ID);
                        }
                    }

                    if (!$found) {
                        if ($optionGroup->isRequired()) {
                            //$this->buildFromOption($child, reset($options));
                            throw new Exception\LogicException('Option group is required.');
                        } else {
                            $item->removeChild($child);
                        }
                    }

                    // Next option group
                    continue 2;
                }
            }
        }

        $this->cleanUpOptionGroups($item, $optionGroupIds);
    }

    /**
     * Builds the item from the option.
     *
     * @param SaleItemInterface $item
     * @param OptionInterface   $option
     * @param int               $choiceCount
     */
    public function buildFromOption(SaleItemInterface $item, OptionInterface $option, int $choiceCount): void
    {
        // Reset net price
        $item->setNetPrice(new Decimal(0));

        if (null !== $product = $option->getProduct()) {
            $this->buildFromProduct($item, $product);
            $item->unsetDatum(self::VARIANT_ID); // Not a variant choice
        } else {
            $designation = sprintf(
                '%s : %s',
                $option->getGroup()->getTitle(),
                $option->getTitle()
            );

            $item
                ->setDesignation($designation)
                ->setReference($option->getReference())
                ->setWeight($option->getWeight() ? clone $option->getWeight() : null)
                ->setTaxGroup($option->getTaxGroup());
        }

        // Override item net price (from product) with option's net price if set
        if (null !== $price = $option->getNetPrice()) {
            $item->setNetPrice(clone $price);
        }

        $item
            ->setDatum(self::OPTION_GROUP_ID, $option->getGroup()->getId())
            ->setDatum(self::OPTION_ID, $option->getId())
            ->setQuantity(new Decimal(1))
            ->setImmutable(true)
            ->setConfigurable(false);

        // If not private and group is required and group has a single option choice
        if (!$item->isPrivate() && $option->getGroup()->isRequired() && (1 === $choiceCount)) {
            $item->setPrivate(true);
        }
    }

    /**
     * Builds the item from component.
     *
     * @param SaleItemInterface  $item
     * @param ComponentInterface $component
     */
    protected function buildFromComponent(SaleItemInterface $item, ComponentInterface $component): void
    {
        $this->buildFromProduct($item, $component->getChild());

        $item
            ->setDatum(self::COMPONENT_ID, $component->getId())
            ->setQuantity(clone $component->getQuantity())
            ->setPrivate(true)
            ->setImmutable(true)
            ->setConfigurable(false);

        if ($price = $component->getNetPrice()) {
            $item->setNetPrice(clone $price);
        }
    }

    /**
     * Builds the item's components.
     *
     * @param SaleItemInterface $item
     *
     * @throws Exception\SubjectException
     */
    protected function buildComponents(SaleItemInterface $item): void
    {
        $product = $this->resolve($item);

        $components = $product->getComponents()->toArray();
        if ($product->getType() === ProductTypes::TYPE_VARIANT) {
            foreach ($product->getParent()->getComponents() as $component) {
                $components[] = $component;
            }
        }

        if (empty($components)) {
            $this->cleanUpComponents($item);

            return;
        }

        $componentIds = [];
        foreach ($components as $component) {
            // Find option group matching item
            foreach ($item->getChildren() as $child) {
                $componentId = intval($child->getDatum(self::COMPONENT_ID));

                // Component id match
                if ($componentId != $component->getId()) {
                    continue; // Next child
                }

                if (in_array($componentId, $componentIds)) {
                    // Remove component duplicate
                    $item->removeChild($child);
                    continue; // Next child
                }

                $componentIds[] = $componentId;

                $this->buildFromComponent($child, $component);

                continue 2; // Next component
            }

            // Not found -> create it
            $child = $item->createChild();

            $this->buildFromComponent($child, $component);

            $componentIds[] = $component->getId();
        }

        $this->cleanUpComponents($item, $componentIds);
    }

    /**
     * Removes irrelevant bundle slot child items.
     *
     * @param SaleItemInterface $item
     * @param array             $bundleSlotIds The bundle slots ids to keep
     */
    private function cleanUpBundleSlots(SaleItemInterface $item, array $bundleSlotIds = []): void
    {
        foreach ($item->getChildren() as $childItem) {
            if (0 < $bundleSlotId = intval($childItem->getDatum(self::BUNDLE_SLOT_ID))) {
                if (!in_array($bundleSlotId, $bundleSlotIds)) {
                    $item->removeChild($childItem);
                }
            }
        }
    }

    /**
     * Removes irrelevant option groups child item.
     *
     * @param SaleItemInterface $item
     * @param array             $optionGroupIds The option groups ids to keep
     */
    private function cleanUpOptionGroups(SaleItemInterface $item, array $optionGroupIds = []): void
    {
        foreach ($item->getChildren() as $childItem) {
            if (0 < $optionGroupId = intval($childItem->getDatum(self::OPTION_GROUP_ID))) {
                if (!in_array($optionGroupId, $optionGroupIds)) {
                    $item->removeChild($childItem);
                }
            }
        }
    }

    /**
     * Removes irrelevant component child item.
     *
     * @param SaleItemInterface $item
     * @param array             $componentIds The components ids to keep
     */
    private function cleanUpComponents(SaleItemInterface $item, array $componentIds = []): void
    {
        foreach ($item->getChildren() as $childItem) {
            if (0 < $componentId = intval($childItem->getDatum(self::COMPONENT_ID))) {
                if (!in_array($componentId, $componentIds)) {
                    $item->removeChild($childItem);
                }
            }
        }
    }

    /**
     * Initializes the sale item (subject must be assigned).
     *
     * @param SaleItemInterface $item    The sale item
     * @param array             $exclude The option group ids to exclude
     */
    public function initialize(SaleItemInterface $item, array $exclude = []): void
    {
        $product = $this->resolve($item);

        // Clear identifiers vars
        $item->unsetDatum(self::VARIANT_ID);
        $item->unsetDatum(self::BUNDLE_SLOT_ID);
        $item->unsetDatum(self::BUNDLE_CHOICE_ID);
        $item->unsetDatum(self::OPTION_GROUP_ID);
        $item->unsetDatum(self::OPTION_ID);
        $item->unsetDatum(self::COMPONENT_ID);

        switch ($product->getType()) {
            case ProductTypes::TYPE_SIMPLE:
                $this->initializeFromSimple($item, $product);
                break;
            case ProductTypes::TYPE_VARIANT:
                $this->initializeFromVariant($item, $product);
                break;
            case ProductTypes::TYPE_VARIABLE:
                $this->initializeFromVariable($item, $product);
                break;
            case ProductTypes::TYPE_BUNDLE:
            case ProductTypes::TYPE_CONFIGURABLE:
                $this->initializeFromBundle($item, $product, $exclude);
                break;
            default:
                throw new Exception\InvalidArgumentException('Unexpected product type');
        }

        $this->initializeOptions($item, $exclude);
    }

    /**
     * Initializes the sale item from the given variable item.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    protected function initializeFromSimple(SaleItemInterface $item, ProductInterface $product): void
    {
        ProductTypes::assertChildType($product);

        $item
            ->setCompound(false)
            ->setConfigurable(false)
            ->setPrivate(!$product->isVisible());
    }

    /**
     * Initializes the sale item from the given variable item.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    protected function initializeFromVariable(SaleItemInterface $item, ProductInterface $product): void
    {
        ProductTypes::assertVariable($product);

        $this->initializeFromVariant($item, $this->fallbackVariableToVariant($product, $item));
    }

    /**
     * Initializes the sale item from the given variant item.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    protected function initializeFromVariant(SaleItemInterface $item, ProductInterface $product): void
    {
        ProductTypes::assertVariant($product);

        $item->setDatum(self::VARIANT_ID, $product->getId());

        $this->initializeFromSimple($item, $product);
    }

    /**
     * Initializes the item from the given bundle (or configurable) product.
     *
     * @param SaleItemInterface $item    The sale item
     * @param ProductInterface  $product The product
     * @param array             $exclude The option group ids to exclude
     */
    protected function initializeFromBundle(SaleItemInterface $item, ProductInterface $product, array $exclude): void
    {
        ProductTypes::assertBundled($product);

        $configurable = $product->getType() === ProductTypes::TYPE_CONFIGURABLE;

        $item
            ->setCompound(true)
            ->setConfigurable($configurable)
            ->setPrivate(!$product->isVisible());

        // Filter bundle slots if configurable
        $bundlesSlots = $configurable
            ? $this->filter->getBundleSlots($product)
            : $product->getBundleSlots()->toArray();

        // For each bundle/configurable slots
        $bundleSlotIds = [];
        foreach ($bundlesSlots as $bundleSlot) {
            // Filter bundle slot choices if configurable
            $bundlesChoices = $configurable
                ? $this->filter->getSlotChoices($bundleSlot)
                : $bundleSlot->getChoices()->toArray();

            /** @var BundleChoiceInterface $defaultChoice */
            $defaultChoice = current($bundlesChoices);
            $choiceProductIds = [];

            // Valid and default slot product(s)
            foreach ($bundlesChoices as $choice) {
                $choiceProduct = $choice->getProduct();
                if ($choiceProduct->getType() === ProductTypes::TYPE_VARIABLE) {
                    // Variable product can't be assigned, so use variants
                    foreach ($choiceProduct->getVariants() as $variant) {
                        $choiceProductIds[] = $variant->getId();
                    }
                } else {
                    $choiceProductIds[] = $choiceProduct->getId();
                }
            }

            // Find slot matching item
            if ($item->hasChildren()) {
                foreach ($item->getChildren() as $child) {
                    // Check bundle slot id
                    $bundleSlotId = intval($child->getDatum(self::BUNDLE_SLOT_ID));
                    if ($bundleSlotId != $bundleSlot->getId()) {
                        continue;
                    }

                    // Remove bundle slot duplicates
                    if (in_array($bundleSlotId, $bundleSlotIds)) {
                        $item->removeChild($child);
                        continue;
                    }
                    $bundleSlotIds[] = $bundleSlotId;

                    // Get/resolve item subject
                    $childProduct = $this->resolve($child);

                    // If invalid choice
                    if (!in_array($childProduct->getId(), $choiceProductIds)) {
                        $child->getSubjectIdentity()->clear();

                        // Initialize default choice
                        $this->initializeFromBundleChoice($child, $defaultChoice, $exclude);
                    }

                    // Next bundle slot
                    continue 2;
                }
            }

            // Item not found : create it
            $child = $item->createChild();

            if ($bundleSlot->isRequired()) {
                $this->initializeFromBundleChoice($child, $defaultChoice, $exclude);
            } else {
                $child->setDatum(self::BUNDLE_SLOT_ID, $bundleSlot->getId());
            }
        }
    }

    /**
     * Initializes the sale item from the given bundle choice.
     *
     * @param SaleItemInterface     $item    The sale item
     * @param BundleChoiceInterface $choice  The bundle choice
     * @param array                 $exclude The option group ids to exclude
     */
    public function initializeFromBundleChoice(
        SaleItemInterface     $item,
        BundleChoiceInterface $choice,
        array                 $exclude = []
    ): void {
        $product = $this->fallbackVariableToVariant($choice->getProduct(), $item);
        $this->provider->assign($item, $product);

        $this->initialize($item, array_unique(array_merge($exclude, $choice->getExcludedOptionGroups())));

        // Override item net price (from product) with choice's net price if set
        if ($price = $choice->getNetPrice()) {
            $item->setNetPrice(clone $price);
        }

        $item
            ->setQuantity($choice->getMinQuantity())
            ->setPosition($choice->getSlot()->getPosition())
            ->setDatum(self::BUNDLE_SLOT_ID, $choice->getSlot()->getId())
            ->setDatum(self::BUNDLE_CHOICE_ID, $choice->getId());
    }

    /**
     * Initializes the sale item's children regarding the product's option groups.
     *
     * @param SaleItemInterface $item    The sale item
     * @param array             $exclude The option groups ids to exclude
     */
    protected function initializeOptions(SaleItemInterface $item, array $exclude): void
    {
        $optionGroups = $this->getOptionGroups($item, $exclude);

        $optionGroupIds = [];

        foreach ($optionGroups as $optionGroup) {
            // Skip if group has no options
            $options = $this->filter->getGroupOptions($optionGroup);
            if (empty($options)) {
                continue;
            }

            // Find option group matching item
            if ($item->hasChildren()) {
                foreach ($item->getChildren() as $child) {
                    // Skip if item has no option group data
                    if (!$child->hasDatum(self::OPTION_GROUP_ID)) {
                        continue;
                    }

                    // Check option group data
                    $optionGroupId = intval($child->getDatum(self::OPTION_GROUP_ID));
                    if ($optionGroupId != $optionGroup->getId()) {
                        continue;
                    }

                    // Remove option group duplicates
                    if (in_array($optionGroupId, $optionGroupIds)) {
                        $item->removeChild($child);
                        continue;
                    }
                    $optionGroupIds[] = $optionGroupId;

                    $child
                        ->setQuantity(new Decimal(1))
                        ->setPosition($optionGroup->getPosition());

                    // Check option choice
                    $found = false;
                    if (0 < $optionId = intval($child->getDatum(self::OPTION_ID))) {
                        foreach ($options as $option) {
                            if ($optionId === $option->getId()) {
                                $found = true;
                                break;
                            }
                        }
                    }

                    // Not Found
                    if (!$found) {
                        $child->unsetDatum(self::OPTION_ID);

                        // Default choice if required
                        if ($optionGroup->isRequired()) {
                            if ($option = current($options)) {
                                $child->setDatum(self::OPTION_ID, $option->getId());
                            }
                        }
                    }

                    // Next option group
                    continue 2;
                }
            }

            // Item not found : create it
            $child = $item->createChild();

            $this->initializeFromOptionGroup($child, $optionGroup);
        }
    }

    /**
     * Initializes the sale item from the given option group.
     *
     * @param SaleItemInterface    $item
     * @param OptionGroupInterface $optionGroup
     */
    public function initializeFromOptionGroup(SaleItemInterface $item, OptionGroupInterface $optionGroup): void
    {
        $item
            ->setDatum(self::OPTION_GROUP_ID, $optionGroup->getId())
            ->setQuantity(new Decimal(1))
            ->setPosition($optionGroup->getPosition());

        // Default choice if required
        if ($optionGroup->isRequired()) {
            // Skip if group has no options
            $options = $this->filter->getGroupOptions($optionGroup);
            if (empty($options)) {
                return;
            }

            if ($option = current($options)) {
                $item->setDatum(self::OPTION_ID, $option->getId());
            }
        }
    }

    /**
     * Returns the available bundle slots for the given sale item.
     *
     * @param SaleItemInterface $item
     *
     * @return BundleSlotInterface[]
     */
    public function getBundleSlots(SaleItemInterface $item): array
    {
        $product = $this->resolve($item);

        return $this->filter->getBundleSlots($product);
    }

    /**
     * Returns the available option groups for the given sale item (merges variable and variant groups).
     *
     * @param SaleItemInterface $item
     * @param array             $exclude = The option group ids to exclude
     *
     * @return OptionGroupInterface[]
     */
    public function getOptionGroups(SaleItemInterface $item, array $exclude = []): array
    {
        $product = $this->resolve($item);

        $groups = $this->filter->getOptionGroups($product, $exclude);

        // Filter product option groups
        if ($product->getType() === ProductTypes::TYPE_VARIANT) {
            $variable = $product->getParent();

            // Filter variant option groups
            $groups = array_merge($this->filter->getOptionGroups($variable, $exclude), $groups);
        }

        return $groups;
    }

    /**
     * Returns the relevant variant if the given product is a variable one.
     *
     * @param ProductInterface       $product
     * @param SaleItemInterface|null $item
     *
     * @return ProductInterface If product has no variant
     */
    private function fallbackVariableToVariant(
        ProductInterface  $product,
        SaleItemInterface $item = null
    ): ProductInterface {
        if ($product->getType() === ProductTypes::TYPE_VARIABLE) {
            $variants = $this->filter->getVariants($product);

            if (empty($variants)) {
                throw new Exception\InvalidArgumentException('Variable product must have at least one variant.');
            }

            if ($item && 0 < ($variantId = intval($item->getDatum(self::VARIANT_ID)))) {
                foreach ($variants as $v) {
                    if ($variantId == $v->getId()) {
                        return $v;
                    }
                }
            }

            return reset($variants);
        }

        return $product;
    }

    /**
     * Resolves the sale item's product.
     *
     * @param SaleItemInterface $item
     *
     * @return ProductInterface
     * @throws Exception\SubjectException
     */
    private function resolve(SaleItemInterface $item): ProductInterface
    {
        $product = $this->provider->resolve($item);

        if (!$product instanceof ProductInterface) {
            throw new UnexpectedTypeException($product, ProductInterface::class);
        }

        return $product;
    }
}

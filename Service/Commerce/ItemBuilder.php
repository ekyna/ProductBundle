<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception;

/**
 * Class ItemBuilder
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ItemBuilder
{
    const VARIANT_ID       = 'variant_id';
    const BUNDLE_SLOT_ID   = 'bundle_slot_id';
    const BUNDLE_CHOICE_ID = 'bundle_choice_id';
    const OPTION_GROUP_ID  = 'option_group_id';
    const OPTION_ID        = 'option_id';
    const COMPONENT_ID     = 'component_id';

    /**
     * @var ProductProvider
     */
    protected $provider;

    /**
     * @var ProductFilterInterface
     */
    protected $filter;


    /**
     * Constructor.
     *
     * @param ProductProvider        $provider
     * @param ProductFilterInterface $filter
     */
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
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Returns the product filter.
     *
     * @return ProductFilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Builds the sale item (subject must be assigned).
     *
     * @param SaleItemInterface $item
     */
    public function build(SaleItemInterface $item)
    {
        /** @var ProductInterface $product */
        $product = $this->provider->resolve($item);

        $this->buildFromProduct($item, $product, []);
    }

    /**
     * Builds the sale item from the given product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array             $exclude The option group ids to exclude
     *
     * @throws Exception\InvalidArgumentException If product type is not supported
     */
    protected function buildFromProduct(SaleItemInterface $item, ProductInterface $product, array $exclude = null)
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
    protected function buildFromSimple(SaleItemInterface $item, ProductInterface $product)
    {
        ProductTypes::assertChildType($product);

        $this->provider->assign($item, $product);

        $item
            ->setDesignation((string)$product)
            ->setReference($product->getReference())
            ->setNetPrice($product->getNetPrice())
            ->setWeight($product->getPackageWeight())
            ->setTaxGroup($product->getTaxGroup())
            ->setCompound(false)
            ->setConfigurable(false)
            ->setPrivate(!$product->isVisible());

        $this->cleanUpBundleSlots($item, []);
    }

    /**
     * Builds the sale item from the given variable product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     *
     * @throws Exception\LogicException If the product has no variant
     */
    protected function buildFromVariable(SaleItemInterface $item, ProductInterface $product)
    {
        ProductTypes::assertVariable($product);

        $variant = null;
        $variants = $this->filter->getVariants($product);

        if (empty($variants)) {
            throw new Exception\LogicException("Variable product must have at least one variant.");
        }

        if (0 < ($variantId = intval($item->getData(self::VARIANT_ID)))) {
            foreach ($variants as $v) {
                if ($variantId == $v->getId()) {
                    $variant = $v;
                    break;
                }
            }
        }

        if (null === $variant) {
            /** @var ProductInterface $variant */
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
    public function buildFromVariant(SaleItemInterface $item, ProductInterface $variant)
    {
        ProductTypes::assertVariant($variant);

        $this->buildFromSimple($item, $variant);

        $item->setData(self::VARIANT_ID, $variant->getId());
    }

    /**
     * Builds the sale item from the given bundle product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array             $exclude The option groups ids to exclude
     */
    protected function buildFromBundle(SaleItemInterface $item, ProductInterface $product, array $exclude = null)
    {
        ProductTypes::assertBundle($product);

        $this->provider->assign($item, $product);

        // Bundle root item
        $item
            ->setDesignation($product->getDesignation())
            ->setReference($product->getReference())
            ->setTaxGroup($product->getTaxGroup())
            ->setCompound(true)
            ->setConfigurable(false)
            ->setPrivate(!$product->isVisible());

        // (Do not filter bundle slots)
        /** @var \Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface[] $bundlesSlots */
        $bundlesSlots = $product->getBundleSlots()->toArray();

        // Every slot must match a single item
        $bundleSlotIds = [];
        foreach ($bundlesSlots as $bundleSlot) {
            $choices = $this->filter->getSlotChoices($bundleSlot);
            if (empty($choices)) {
                continue;
            }

            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $bundleChoice */
            $bundleChoice = current($choices);

            // Find matching item
            foreach ($item->getChildren() as $childItem) {
                $bundleSlotId = intval($childItem->getData(self::BUNDLE_SLOT_ID));
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
    }

    /**
     * Builds the sale item from the given configurable product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array             $exclude
     */
    protected function buildFromConfigurable(SaleItemInterface $item, ProductInterface $product, array $exclude = null)
    {
        ProductTypes::assertConfigurable($product);

        $this->provider->assign($item, $product);

        // Configurable root item
        $item
            ->setDesignation($product->getDesignation())
            ->setReference($product->getReference())
            ->setTaxGroup($product->getTaxGroup())
            ->setCompound(true)
            ->setConfigurable(true)
            ->setPrivate(false);

        // Filter bundle slots
        $bundlesSlots = $this->filter->getBundleSlots($product);

        // Every slot must match a single item
        $bundleSlotIds = [];
        foreach ($bundlesSlots as $bundleSlot) {
            $choices = $this->filter->getSlotChoices($bundleSlot);
            if (empty($choices)) {
                continue;
            }

            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $bundleChoice */
            $bundleChoice = current($choices);

            // Find matching item
            foreach ($item->getChildren() as $childItem) {
                $bundleSlotId = intval($childItem->getData(self::BUNDLE_SLOT_ID));
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
                if (0 < $bundleChoiceId = intval($childItem->getData(self::BUNDLE_CHOICE_ID))) {
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
            if ($child->hasData(self::BUNDLE_SLOT_ID)) {
                $child->setPrivate(false);
            }
        }
    }

    /**
     * Builds the sale item from the given bundle choice.
     *
     * @param SaleItemInterface           $item    The sale item
     * @param Model\BundleChoiceInterface $choice  The bundle choice
     * @param array                       $exclude The option group ids to exclude
     */
    protected function buildFromBundleChoice(
        SaleItemInterface $item,
        Model\BundleChoiceInterface $choice,
        array $exclude = null
    ) {
        if (!is_null($exclude)) {
             $exclude = array_unique(array_merge($exclude, $choice->getExcludedOptionGroups()));
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
        if (null !== $choice->getNetPrice()) {
            $item->setNetPrice($choice->getNetPrice());
        }

        $item
            ->setData(self::BUNDLE_SLOT_ID, $choice->getSlot()->getId())
            ->setData(self::BUNDLE_CHOICE_ID, $choice->getId())
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
    protected function buildOptions(SaleItemInterface $item, array $exclude)
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
                    $optionGroupId = intval($child->getData(self::OPTION_GROUP_ID));
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
                        ->setQuantity(1)
                        ->setPosition($optionGroup->getPosition());

                    // Check option choice
                    $found = false;
                    if (0 < $optionId = intval($child->getData(self::OPTION_ID))) {
                        foreach ($options as $option) {
                            if ($optionId === $option->getId()) {
                                $found = true;

                                $this->buildFromOption($child, $option, count($options));

                                break;
                            }
                        }
                        // Not Found => unset choice
                        if (!$found) {
                            $child->unsetData(self::OPTION_ID);
                        }
                    }

                    if (!$found) {
                        if ($optionGroup->isRequired()) {
                            //$this->buildFromOption($child, reset($options));
                            throw new Exception\LogicException("Option group is required.");
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
     * @param SaleItemInterface     $item
     * @param Model\OptionInterface $option
     * @param int                   $choiceCount
     */
    public function buildFromOption(SaleItemInterface $item, Model\OptionInterface $option, int $choiceCount)
    {
        // Reset net price
        $item->setNetPrice(0);

        if (null !== $product = $option->getProduct()) {
            $this->buildFromProduct($item, $product, []); // TODO Cascade / exclude
            $item->unsetData(self::VARIANT_ID); // Not a variant choice
        } else {
            $designation = sprintf(
                '%s : %s',
                $option->getGroup()->getName(),
                $option->getDesignation()
            );

            $item
                ->setDesignation($designation)
                ->setReference($option->getReference())
                ->setWeight($option->getWeight())
                ->setTaxGroup($option->getTaxGroup());
        }

        // Override item net price (from product) with option's net price if set
        if (null !== $option->getNetPrice()) {
            $item->setNetPrice($option->getNetPrice());
        }

        $item
            ->setData(self::OPTION_GROUP_ID, $option->getGroup()->getId())
            ->setData(self::OPTION_ID, $option->getId())
            ->setQuantity(1)
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
     * @param SaleItemInterface        $item
     * @param Model\ComponentInterface $component
     */
    protected function buildFromComponent(SaleItemInterface $item, Model\ComponentInterface $component)
    {
        $this->buildFromProduct($item, $component->getChild(), null);

        $item
            ->setQuantity($component->getQuantity())
            ->setPrivate(true)
            ->setImmutable(true)
            ->setConfigurable(false);

        if (!is_null($price = $component->getNetPrice())) {
            $item->setNetPrice($price);
        }
    }

    /**
     * Builds the item's components.
     *
     * @param SaleItemInterface $item
     *
     * @throws Exception\SubjectException
     */
    protected function buildComponents(SaleItemInterface $item)
    {
        $product = $this->provider->resolve($item);

        // TODO (?) $components = $this->filter->getComponents($product);
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
                $componentId = intval($child->getData(self::COMPONENT_ID));

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
        }

        $this->cleanUpComponents($item, $componentIds);
    }

    /**
     * Removes irrelevant bundle slot child items.
     *
     * @param SaleItemInterface $item
     * @param array             $bundleSlotIds The bundle slots ids to keep
     */
    private function cleanUpBundleSlots(SaleItemInterface $item, array $bundleSlotIds = [])
    {
        foreach ($item->getChildren() as $childItem) {
            if (0 < $bundleSlotId = intval($childItem->getData(self::BUNDLE_SLOT_ID))) {
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
    private function cleanUpOptionGroups(SaleItemInterface $item, array $optionGroupIds = [])
    {
        foreach ($item->getChildren() as $childItem) {
            if (0 < $optionGroupId = intval($childItem->getData(self::OPTION_GROUP_ID))) {
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
    private function cleanUpComponents(SaleItemInterface $item, array $componentIds = [])
    {
        foreach ($item->getChildren() as $childItem) {
            if (0 < $componentId = intval($childItem->getData(self::COMPONENT_ID))) {
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
    public function initialize(SaleItemInterface $item, array $exclude = [])
    {
        /** @var ProductInterface $product */
        $product = $this->provider->resolve($item);

        // Clear identifiers vars
        $item->unsetData(self::VARIANT_ID);
        $item->unsetData(self::BUNDLE_SLOT_ID);
        $item->unsetData(self::BUNDLE_CHOICE_ID);
        $item->unsetData(self::OPTION_GROUP_ID);
        $item->unsetData(self::OPTION_ID);
        $item->unsetData(self::COMPONENT_ID);

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
    protected function initializeFromSimple(SaleItemInterface $item, ProductInterface $product)
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
    protected function initializeFromVariable(SaleItemInterface $item, ProductInterface $product)
    {
        ProductTypes::assertVariable($product);

        $this->initializeFromVariant($item, $this->fallbackVariableToVariant($product));
    }

    /**
     * Initializes the sale item from the given variant item.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    protected function initializeFromVariant(SaleItemInterface $item, ProductInterface $product)
    {
        ProductTypes::assertVariant($product);

        $item->setData(self::VARIANT_ID, $product->getId());

        $this->initializeFromSimple($item, $product);
    }

    /**
     * Initializes the item from the given bundle (or configurable) product.
     *
     * @param SaleItemInterface $item    The sale item
     * @param ProductInterface  $product The product
     * @param array             $exclude The option group ids to exclude
     */
    protected function initializeFromBundle(SaleItemInterface $item, ProductInterface $product, array $exclude)
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

            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $defaultChoice */
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
                    $bundleSlotId = intval($child->getData(self::BUNDLE_SLOT_ID));
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
                    $childProduct = $this->provider->resolve($child);

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
            /** @var SaleItemInterface $child */
            $child = $item->createChild();

            if ($bundleSlot->isRequired()) {
                $this->initializeFromBundleChoice($child, $defaultChoice, $exclude);
            } else {
                $child->setData(self::BUNDLE_SLOT_ID, $bundleSlot->getId());
            }
        }
    }

    /**
     * Initializes the sale item from the given bundle choice.
     *
     * @param SaleItemInterface           $item    The sale item
     * @param Model\BundleChoiceInterface $choice  The bundle choice
     * @param array                       $exclude The option group ids to exclude
     */
    public function initializeFromBundleChoice(
        SaleItemInterface $item,
        Model\BundleChoiceInterface $choice,
        array $exclude = []
    ) {
        $product = $this->fallbackVariableToVariant($choice->getProduct());
        $this->provider->assign($item, $product);

        $this->initialize($item, array_unique(array_merge($exclude, $choice->getExcludedOptionGroups())));

        // Override item net price (from product) with choice's net price if set
        if (null !== $choice->getNetPrice()) {
            $item->setNetPrice($choice->getNetPrice());
        }

        $item
            ->setQuantity($choice->getMinQuantity())
            ->setPosition($choice->getSlot()->getPosition())
            ->setData(self::BUNDLE_SLOT_ID, $choice->getSlot()->getId())
            ->setData(self::BUNDLE_CHOICE_ID, $choice->getId());
    }

    /**
     * Initializes the sale item's children regarding to the product's option groups.
     *
     * @param SaleItemInterface $item    The sale item
     * @param array             $exclude The option groups ids to exclude
     */
    protected function initializeOptions(SaleItemInterface $item, array $exclude)
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
                    if (!$child->hasData(self::OPTION_GROUP_ID)) {
                        continue;
                    }

                    // Check option group data
                    $optionGroupId = intval($child->getData(self::OPTION_GROUP_ID));
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
                        ->setQuantity(1)
                        ->setPosition($optionGroup->getPosition());

                    // Check option choice
                    $found = false;
                    if (0 < $optionId = intval($child->getData(self::OPTION_ID))) {
                        foreach ($options as $option) {
                            if ($optionId === $option->getId()) {
                                $found = true;
                                break;
                            }
                        }
                    }

                    // Not Found
                    if (!$found) {
                        $child->unsetData(self::OPTION_ID);

                        // Default choice if required
                        if ($optionGroup->isRequired()) {
                            /** @var \Ekyna\Bundle\ProductBundle\Model\OptionInterface $option */
                            if ($option = current($options)) {
                                $child->setData(self::OPTION_ID, $option->getId());
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
     * @param SaleItemInterface          $item
     * @param Model\OptionGroupInterface $optionGroup
     */
    public function initializeFromOptionGroup(SaleItemInterface $item, Model\OptionGroupInterface $optionGroup)
    {
        $item
            ->setData(self::OPTION_GROUP_ID, $optionGroup->getId())
            ->setQuantity(1)
            ->setPosition($optionGroup->getPosition());

        // Default choice if required
        if ($optionGroup->isRequired()) {
            // Skip if group has no options
            $options = $this->filter->getGroupOptions($optionGroup);
            if (empty($options)) {
                return;
            }

            /** @var \Ekyna\Bundle\ProductBundle\Model\OptionInterface $option */
            if ($option = current($options)) {
                $item->setData(self::OPTION_ID, $option->getId());
            }
        }
    }

    /**
     * Returns the available bundle slots for the given sale item.
     *
     * @param SaleItemInterface $item
     *
     * @return Model\BundleSlotInterface[]
     */
    public function getBundleSlots(SaleItemInterface $item)
    {
        /** @var ProductInterface $product */
        $product = $this->provider->resolve($item);

        return $this->filter->getBundleSlots($product);
    }

    /**
     * Returns the available option groups for the given sale item (merges variable and variant groups).
     *
     * @param SaleItemInterface $item
     * @param array             $exclude = The option group ids to exclude
     *
     * @return Model\OptionGroupInterface[]
     */
    public function getOptionGroups(SaleItemInterface $item, array $exclude = [])
    {
        /** @var ProductInterface $product */
        $product = $this->provider->resolve($item);

        $groups = $this->filter->getOptionGroups($product, $exclude);

        // Filter product option groups
        if ($product->getType() === ProductTypes::TYPE_VARIANT) {
            /** @var ProductInterface $variable */
            $variable = $product->getParent();

            // Filter variant option groups
            $groups = array_merge($this->filter->getOptionGroups($variable, $exclude), $groups);
        }

        return $groups;
    }

    /**
     * Returns the relevant variant if the given product is a variable one.
     *
     * @param ProductInterface  $product
     * @param SaleItemInterface $item
     *
     * @return ProductInterface If product has no variant
     */
    private function fallbackVariableToVariant(ProductInterface $product, SaleItemInterface $item = null)
    {
        if ($product->getType() === ProductTypes::TYPE_VARIABLE) {
            $variants = $this->filter->getVariants($product);

            if (empty($variants)) {
                throw new Exception\InvalidArgumentException("Variable product must have at least one variant.");
            }

            if ($item && 0 < ($variantId = intval($item->getData(self::VARIANT_ID)))) {
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
}

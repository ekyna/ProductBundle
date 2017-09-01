<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;

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

    /**
     * @var ProductProvider
     */
    private $provider;

    /**
     * @var ProductFilterInterface
     */
    private $filter;


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
        $product = $this->provider->resolve($item);

        $this->buildFromProduct($item, $product);
    }

    /**
     * Builds the sale item from the given product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    public function buildFromProduct(SaleItemInterface $item, ProductInterface $product)
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
                $this->buildFromBundle($item, $product);
                break;
            case ProductTypes::TYPE_CONFIGURABLE:
                $this->buildFromConfigurable($item, $product);
                break;
            default:
                throw new InvalidArgumentException('Unexpected product type');
        }

        $this->buildOptions($item);
    }

    /**
     * Builds the sale item from the simple product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    public function buildFromSimple(SaleItemInterface $item, ProductInterface $product)
    {
        ProductTypes::assertChildType($product);

        $this->provider->assign($item, $product);

        $item
            ->setDesignation((string)$product)
            ->setReference($product->getReference())
            ->setNetPrice($product->getNetPrice())
            ->setWeight($product->getWeight())
            ->setTaxGroup($product->getTaxGroup());
    }

    /**
     * Builds the sale item from the given variable product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    public function buildFromVariable(SaleItemInterface $item, ProductInterface $product)
    {
        ProductTypes::assertVariable($product);

        $variant = null;
        $variants = $this->filter->getVariants($product);

        if (empty($variants)) {
            throw new InvalidArgumentException("Variable product must have at least one variant.");
        }

        if (0 < ($variantId = intval($item->getData(static::VARIANT_ID)))) {
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

        $item->setData(static::VARIANT_ID, $variant->getId());
    }

    /**
     * Builds the sale item from the given bundle product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    public function buildFromBundle(SaleItemInterface $item, ProductInterface $product)
    {
        ProductTypes::assertBundle($product);

        $this->provider->assign($item, $product);

        // Bundle root item
        $item
            ->setDesignation($product->getDesignation())
            ->setReference($product->getReference())
            ->setCompound(true);

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
                $bundleSlotId = intval($childItem->getData(static::BUNDLE_SLOT_ID));
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
                $this->buildFromBundleChoice($childItem, $bundleChoice);

                continue 2;
            }

            $bundleSlotIds[] = $bundleSlot->getId();

            // Not found : Create and build the item from the bundle choice's product
            $this->buildFromBundleChoice($item->createChild(), $bundleChoice);
        }

        $this->cleanUpBundleSlots($item, $bundleSlotIds);
    }

    /**
     * Builds the sale item from the given configurable product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    public function buildFromConfigurable(SaleItemInterface $item, ProductInterface $product)
    {
        ProductTypes::assertConfigurable($product);

        $this->provider->assign($item, $product);

        // Configurable root item
        $item
            ->setDesignation($product->getDesignation())
            ->setReference($product->getReference())
            ->setCompound(true)
            ->setConfigurable(true);

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
                $bundleSlotId = intval($childItem->getData(static::BUNDLE_SLOT_ID));
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
                if (0 < $bundleChoiceId = intval($childItem->getData(static::BUNDLE_CHOICE_ID))) {
                    foreach ($choices as $choice) {
                        if ($bundleChoiceId === $choice->getId()) {
                            $bundleChoice = $choice;
                            break;
                        }
                    }

                    // Build the item from the bundle choice's product
                    $this->buildFromBundleChoice($childItem, $bundleChoice);

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
                $this->buildFromBundleChoice($item->createChild(), $bundleChoice);
            }
        }

        $this->cleanUpBundleSlots($item, $bundleSlotIds);
    }

    /**
     * Builds the sale item from the given bundle choice.
     *
     * @param SaleItemInterface           $item
     * @param Model\BundleChoiceInterface $bundleChoice
     */
    public function buildFromBundleChoice(SaleItemInterface $item, Model\BundleChoiceInterface $bundleChoice)
    {
        $this->buildFromProduct($item, $bundleChoice->getProduct());

        // Normalize quantity
        if ($item->getQuantity() < $bundleChoice->getMinQuantity()) {
            $item->setQuantity($bundleChoice->getMinQuantity());
        } elseif ($item->getQuantity() > $bundleChoice->getMaxQuantity()) {
            $item->setQuantity($bundleChoice->getMaxQuantity());
        }

        $item
            ->setData(static::BUNDLE_SLOT_ID, $bundleChoice->getSlot()->getId())
            ->setData(static::BUNDLE_CHOICE_ID, $bundleChoice->getId())
            ->setImmutable(true);
    }

    /**
     * Builds the item's options.
     *
     * @param SaleItemInterface $item
     */
    public function buildOptions(SaleItemInterface $item)
    {
        $optionGroups = $this->getOptionGroups($item);

        if (empty($optionGroups)) {
            $this->cleanUpOptionGroups($item);

            return;
        }

        $item->setConfigurable(true);

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
                    $optionGroupId = intval($child->getData(static::OPTION_GROUP_ID));
                    if ($optionGroupId != $optionGroup->getId()) {
                        continue;
                    }

                    // Remove bundle slot duplicates
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
                    if (0 < $optionId = intval($child->getData(static::OPTION_ID))) {
                        foreach ($options as $option) {
                            if ($optionId === $option->getId()) {
                                $found = true;

                                $this->buildFromOption($child, $option);

                                break;
                            }
                        }
                        // Not Found => unset choice
                        if (!$found) {
                            $child->unsetData(static::OPTION_ID);
                        }
                    }

                    if (!$found) {
                        if ($optionGroup->isRequired()) {
                            //$this->buildFromOption($child, reset($options));
                            throw new RuntimeException("Option group is required.");
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
     */
    public function buildFromOption(SaleItemInterface $item, Model\OptionInterface $option)
    {
        if (null !== $product = $option->getProduct()) {
            $this->buildFromProduct($item, $product);
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

        $item
            ->setData(static::OPTION_GROUP_ID, $option->getGroup()->getId())
            ->setData(static::OPTION_ID, $option->getId())
            ->setNetPrice($option->getNetPrice())
            ->setQuantity(1)
            ->setImmutable(true);
    }

    /**
     * Removes irrelevant bundle slot child items.
     *
     * @param SaleItemInterface $item
     * @param array             $bundleSlotIds
     */
    private function cleanUpBundleSlots(SaleItemInterface $item, array $bundleSlotIds = [])
    {
        foreach ($item->getChildren() as $childItem) {
            if (0 < $bundleSlotId = intval($childItem->getData(static::BUNDLE_SLOT_ID))) {
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
     * @param array             $optionGroupIds
     */
    private function cleanUpOptionGroups(SaleItemInterface $item, array $optionGroupIds = [])
    {
        foreach ($item->getChildren() as $childItem) {
            if (0 < $optionGroupId = intval($childItem->getData(static::OPTION_GROUP_ID))) {
                if (!in_array($optionGroupId, $optionGroupIds)) {
                    $item->removeChild($childItem);
                }
            }
        }
    }

    /**
     * Initializes the sale item (subject must be assigned).
     *
     * @param SaleItemInterface $item
     */
    public function initialize(SaleItemInterface $item)
    {
        $product = $this->provider->resolve($item);

        if ($product->getType() === ProductTypes::TYPE_VARIANT) {
            $this->initializeFromVariant($item, $product);

        } elseif ($product->getType() === ProductTypes::TYPE_VARIABLE) {
            $this->initializeFromVariable($item, $product);

        } elseif (in_array($product->getType(), [ProductTypes::TYPE_BUNDLE, ProductTypes::TYPE_CONFIGURABLE])) {
            $this->initializeFromBundle($item, $product);
        }

        $this->initializeOptions($item);
    }

    /**
     * Initializes the sale item from the given variable item.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    public function initializeFromVariable(SaleItemInterface $item, ProductInterface $product)
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
    public function initializeFromVariant(SaleItemInterface $item, ProductInterface $product)
    {
        ProductTypes::assertVariant($product);

        $item->setData(static::VARIANT_ID, $product->getId());
    }

    /**
     * Initializes the item from the given bundle (or configurable) product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    public function initializeFromBundle(SaleItemInterface $item, ProductInterface $product)
    {
        // Filter bundle slots
        $bundlesSlots = $this->filter->getBundleSlots($product);

        // For each bundle/configurable slots
        $bundleSlotIds = [];
        foreach ($bundlesSlots as $bundleSlot) {
            // Filter bundle slots
            $bundlesChoices = $this->filter->getSlotChoices($bundleSlot);

            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $defaultChoice */
            $defaultChoice = current($bundlesChoices);
            $choiceProducts = [];

            // Valid and default slot product(s)
            foreach ($bundlesChoices as $choice) {
                $choiceProduct = $choice->getProduct();
                if ($choiceProduct->getType() === ProductTypes::TYPE_VARIABLE) {
                    // Variable product can't be assigned, so use variants
                    foreach ($choiceProduct->getVariants() as $variant) {
                        $choiceProducts[] = $variant;
                    }
                } else {
                    $choiceProducts[] = $choiceProduct;
                }
            }

            // Find slot matching item
            if ($item->hasChildren()) {
                foreach ($item->getChildren() as $child) {
                    // Check bundle slot id
                    $bundleSlotId = intval($child->getData(static::BUNDLE_SLOT_ID));
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
                    if (!in_array($childProduct, $choiceProducts)) {
                        $child->getSubjectIdentity()->clear();

                        // Initialize default choice
                        $this->initializeFromBundleChoice($child, $defaultChoice);
                    }

                    // Next bundle slot
                    continue 2;
                }
            }

            // Item not found : create it
            /** @var SaleItemInterface $child */
            $child = $item->createChild();

            if ($bundleSlot->isRequired()) {
                $this->initializeFromBundleChoice($child, $defaultChoice);
            } else {
                $child->setData(static::BUNDLE_SLOT_ID, $bundleSlot->getId());
            }
        }
    }

    /**
     * Initializes the sale item from the given bundle choice.
     *
     * @param SaleItemInterface           $item
     * @param Model\BundleChoiceInterface $bundleChoice
     */
    public function initializeFromBundleChoice(SaleItemInterface $item, Model\BundleChoiceInterface $bundleChoice)
    {
        //if (!$item->hasSubjectIdentity()) {
            $product = $this->fallbackVariableToVariant($bundleChoice->getProduct());
            $this->provider->assign($item, $product);
        //}

        $this->initialize($item);

        $item
            ->setQuantity($bundleChoice->getMinQuantity())
            ->setPosition($bundleChoice->getSlot()->getPosition())
            ->setData(static::BUNDLE_SLOT_ID, $bundleChoice->getSlot()->getId())
            ->setData(static::BUNDLE_CHOICE_ID, $bundleChoice->getId());
    }

    /**
     * Initializes the sale item's children regarding to the product's option groups.
     *
     * @param SaleItemInterface $item
     */
    public function initializeOptions(SaleItemInterface $item)
    {
        $optionGroups = $this->getOptionGroups($item);

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
                    if (!$child->hasData(static::OPTION_GROUP_ID)) {
                        continue;
                    }

                    // Check option group data
                    $optionGroupId = intval($child->getData(static::OPTION_GROUP_ID));
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
                    if (0 < $optionId = intval($child->getData(static::OPTION_ID))) {
                        foreach ($options as $option) {
                            if ($optionId === $option->getId()) {
                                $found = true;
                                break;
                            }
                        }
                    }

                    // Not Found
                    if (!$found) {
                        $child->unsetData(static::OPTION_ID);
                        // Default choice if required
                        if ($optionGroup->isRequired()) {
                            /** @var \Ekyna\Bundle\ProductBundle\Model\OptionInterface $option */
                            if ($option = current($options)) {
                                $child->setData(static::OPTION_ID, $option->getId());
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
            ->setData(static::OPTION_GROUP_ID, $optionGroup->getId())
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
                $item->setData(static::OPTION_ID, $option->getId());
            }
        }
    }

    /**
     * Returns the available variants for the given sale item.
     *
     * @param SaleItemInterface $item
     *
     * @return Model\ProductInterface[]
     */
    public function getVariants(SaleItemInterface $item)
    {
        $product = $this->provider->resolve($item);

        return $this->filter->getVariants($product);
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
        $product = $this->provider->resolve($item);

        return $this->filter->getBundleSlots($product);
    }

    /**
     * Returns the available option groups for the given sale item (merges variable and variant groups).
     *
     * @param SaleItemInterface $item
     *
     * @return Model\OptionGroupInterface[]
     */
    public function getOptionGroups(SaleItemInterface $item)
    {
        $product = $this->provider->resolve($item);

        // Filter product option groups
        if ($product->getType() === ProductTypes::TYPE_VARIANT) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $variable */
            $variable = $product->getParent();

            // Filter variant option groups
            $groups = array_merge(
                $this->filter->getOptionGroups($variable),
                $this->filter->getOptionGroups($product)
            );
        } else {
            $groups = $this->filter->getOptionGroups($product);
        }

        return $groups;
    }

    /**
     * Returns the relevant variant if the given product is a variable one.
     *
     * @param ProductInterface  $product
     * @param SaleItemInterface $item
     *
     * @return ProductInterface
     */
    private function fallbackVariableToVariant(ProductInterface $product, SaleItemInterface $item = null)
    {
        if ($product->getType() === ProductTypes::TYPE_VARIABLE) {
            $variants = $this->filter->getVariants($product);

            if (empty($variants)) {
                throw new InvalidArgumentException("Variable product must have at least one variant.");
            }

            if ($item && 0 < ($variantId = intval($item->getData(static::VARIANT_ID)))) {
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

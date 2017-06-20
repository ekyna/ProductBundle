<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface;
use Ekyna\Bundle\ProductBundle\Model\OptionGroupInterface;
use Ekyna\Bundle\ProductBundle\Model\OptionInterface;
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
     * Constructor.
     *
     * @param ProductProvider $provider
     */
    public function __construct(ProductProvider $provider)
    {
        $this->provider = $provider;
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

        $this->buildOptions($item, $product);
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

        if (0 < ($variantId = intval($item->getData(static::VARIANT_ID)))) {
            foreach ($product->getVariants() as $v) {
                if ($variantId == $v->getId()) {
                    $variant = $v;
                    break;
                }
            }
        }

        if (null === $variant) {
            /** @var ProductInterface $variant */
            $variant = $product->getVariants()->first();
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

        // Assigns the parent's variable
        $this->provider->assign($item, $variant->getParent());

        $item
            ->setDesignation((string)$variant)
            ->setReference($variant->getReference())
            ->setNetPrice($variant->getNetPrice())
            ->setWeight($variant->getWeight())
            ->setTaxGroup($variant->getTaxGroup())
            ->setData(static::VARIANT_ID, $variant->getId());
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

        // Every slot must match a single item
        $bundleProductIds = [];
        $bundleSlotIds = [];
        foreach ($product->getBundleSlots() as $bundleSlot) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $bundleChoice */
            $bundleChoice = $bundleSlot->getChoices()->first();
            $bundleProduct = $bundleChoice->getProduct();
            $bundleProductIds[] = $bundleProduct->getId();

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

                /** @var ProductInterface $childItemProduct */
                $childItemProduct = $this->provider->resolve($childItem);

                // Build the item from the bundle choice's product
                $this->buildFromProduct($childItem, $childItemProduct);
                $childItem->setData(static::BUNDLE_SLOT_ID, $bundleSlotId);

                // Item is immutable
                $childItem->setImmutable(true);

                continue 2;
            }

            // Not found : call prepareItem() first.
            //TODO$childItem = $item->createChild();
            //$this->bui
            throw new RuntimeException("Bundle slot matching item not found.");
        }

        // Removes miss match items
        /* if ($removeMissMatch) {
            // TODO Don't remove options
            foreach ($item->getChildren() as $childItem) {
                $childProduct = $this->provider->resolve($childItem);
                if (null === $childProduct || !in_array($childProduct->getId(), $bundleProductIds)) {
                    $item->removeChild($childItem);
                }
            }
        }*/
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

        // Every slot must match a single item
        $bundleSlotIds = [];
        foreach ($product->getBundleSlots() as $bundleSlot) {
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

                /** @var ProductInterface $childItemProduct */
                $childItemProduct = $this->provider->resolve($childItem);

                // Sets the bundle product
                $this->buildFromProduct($childItem, $childItemProduct);
                $childItem->setData(static::BUNDLE_SLOT_ID, $bundleSlotId);

                // Item is immutable
                $childItem->setImmutable(true);

                continue 2;
            }

            throw new RuntimeException("Bundle slot matching item not found.");
        }

        // Removes miss match items
//        if ($removeMissMatch) {
//            // TODO Don't remove options ...
//            foreach ($item->getChildren() as $childItem) {
//                /** @var ProductInterface $childProduct */
//                $childProduct = $this->provider->resolve($childItem);
//                if (null === $childProduct) {
//                    $item->removeChild($childItem);
//                    continue;
//                }
//
//                // Find matching slot
//                $bundleSlotId = intval($childItem->getData(static::BUNDLE_SLOT_ID));
//                $bundleSlots = $product->getBundleSlots()->matching(
//                    Criteria::create()->where(Criteria::expr()->eq('id', $bundleSlotId))
//                );
//                if (1 != $bundleSlots->count()) {
//                    $item->removeChild($childItem);
//                    continue;
//                }
//
//                $slotProductsIds = [];
//                $bundleSlot = $bundleSlots->first();
//                /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $bundleChoice */
//                foreach ($bundleSlot->getChoices() as $bundleChoice) {
//                    $slotProductsIds[] = $bundleChoice->getProduct()->getId();
//                }
//
//                if (!in_array($childProduct->getId(), $slotProductsIds)) {
//                    $item->removeChild($childItem);
//                }
//            }
//        }
    }

    /**
     * Builds the sale item from the given bundle choice.
     *
     * @param SaleItemInterface     $item
     * @param BundleChoiceInterface $bundleChoice
     */
    public function buildFromBundleChoice(SaleItemInterface $item, BundleChoiceInterface $bundleChoice)
    {
        $this->buildFromProduct($item, $bundleChoice->getProduct());

        $item
            ->setData(static::BUNDLE_SLOT_ID, $bundleChoice->getSlot()->getId())
            ->setData(static::BUNDLE_CHOICE_ID, $bundleChoice->getId())
            ->setImmutable(true);
    }

    /**
     * Builds the item's options.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    public function buildOptions(SaleItemInterface $item, ProductInterface $product)
    {
        $optionGroups = $this->getProductOptionGroups($item);

        if (empty($optionGroups)) {
            // TODO Remove item children related to options ?
            return;
        }

        $item->setConfigurable(true);

        $optionGroupIds = [];
        foreach ($product->getOptionGroups() as $optionGroup) {
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
                        foreach ($optionGroup->getOptions() as $option) {
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
    }

    /**
     * Builds the item from the option.
     *
     * @param SaleItemInterface $item
     * @param OptionInterface   $option
     */
    public function buildFromOption(SaleItemInterface $item, OptionInterface $option)
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
                ->setNetPrice($option->getNetPrice())
                ->setWeight($option->getWeight())
                ->setQuantity(1)
                ->setTaxGroup($option->getTaxGroup());
        }

        $item
            ->setData(static::OPTION_GROUP_ID, $option->getGroup()->getId())
            ->setData(static::OPTION_ID, $option->getId())
            ->setImmutable(true);
    }

    /**
     * Returns the option groups for the given sale item (merges variable and variant groups).
     *
     * @param SaleItemInterface $item
     *
     * @return OptionGroupInterface[]
     */
    public function getProductOptionGroups($item)
    {
        $product = $this->provider->resolve($item);
        $optionsGroups = $product->getOptionGroups()->toArray();

        if ($product->getType() === ProductTypes::TYPE_VARIABLE && $item->hasData(ItemBuilder::VARIANT_ID)) {
            if (0 < $variantId = intval($item->getData(ItemBuilder::VARIANT_ID))) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\ProductInterface $variant */
                $variant = $this->provider->getProductRepository()->find($variantId);
                foreach ($variant->getOptionGroups() as $optionGroup) {
                    array_push($optionsGroups, $optionGroup);
                }
            };
        }

        return $optionsGroups;
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

        // TODO Initialization is done by the option groups form type.
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

        /** @var ProductInterface $variant */
        $variant = $product->getVariants()->first();

        $this->initializeFromVariant($item, $variant);
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
        // For each bundle/configurable slots
        $bundleSlotIds = [];
        foreach ($product->getBundleSlots() as $bundleSlot) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $defaultChoice */
            $defaultChoice = $bundleSlot->getChoices()->first();
            $choiceProducts = [];

            // Valid and default slot product(s)
            foreach ($bundleSlot->getChoices() as $choice) {
                $choiceProducts[] = $choice->getProduct();
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

                    // TODO Work with static::BUNDLE_CHOICE_ID ?

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

            $this->initializeFromBundleChoice($child, $defaultChoice);
        }

        // TODO Sort items by position ?
    }

    /**
     * Initializes the sale item from the given bundle choice.
     *
     * @param SaleItemInterface     $item
     * @param BundleChoiceInterface $bundleChoice
     */
    public function initializeFromBundleChoice(SaleItemInterface $item, BundleChoiceInterface $bundleChoice)
    {
        $this->provider->assign($item, $bundleChoice->getProduct());

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
        $optionGroups = $this->getProductOptionGroups($item);

        $optionGroupIds = [];
        foreach ($optionGroups as $optionGroup) {
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
                        // TODO Check integrity (variable or variant options groups / bundle slots)
                        ->setPosition($optionGroup->getPosition());

                    // Check option choice
                    $found = false;
                    if (0 < $optionId = intval($child->getData(static::OPTION_ID))) {
                        foreach ($optionGroup->getOptions() as $option) {
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
                            if ($option = $optionGroup->getOptions()->first()) {
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
     * @param SaleItemInterface    $item
     * @param OptionGroupInterface $optionGroup
     */
    public function initializeFromOptionGroup(SaleItemInterface $item, OptionGroupInterface $optionGroup)
    {
        $item
            ->setData(static::OPTION_GROUP_ID, $optionGroup->getId())
            ->setQuantity(1)
            // TODO Check integrity (variable or variant options groups / bundle slots)
            ->setPosition($optionGroup->getPosition());

        // Default choice if required
        if ($optionGroup->isRequired()) {
            /** @var \Ekyna\Bundle\ProductBundle\Model\OptionInterface $option */
            if ($option = $optionGroup->getOptions()->first()) {
                $item->setData(static::OPTION_ID, $option->getId());
            }
        }
    }
}

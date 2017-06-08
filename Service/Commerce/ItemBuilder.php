<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

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
    const VARIANT_ID        = 'variant_id';
    const BUNDLE_SLOT_ID    = 'bundle_slot_id';
    const OPTION_GROUP_ID   = 'option_group_id';
    const OPTION_ID         = 'option_id';

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
     * @inheritdoc
     */
    public function build(SaleItemInterface $item)
    {
        // TODO assert ProductInterface
        $product = $this->provider->resolve($item);

        $this->buildFromProduct($item, $product);

        $this->buildItemOptions($item, $product);
    }

    /**
     * Builds the sale item from the product.
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
    }

    /**
     * Builds the sale item form the simple product.
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
            ->setWeight($product->getWeight())
            ->setTaxGroup($product->getTaxGroup());
    }

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
     * Builds the sale item form the variable product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    protected function buildFromVariable(SaleItemInterface $item, ProductInterface $product)
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
            throw new RuntimeException("Failed to resolve variable's selected variant.");
            //$variant = $product->getVariants()->first();
        }

        $this->buildFromVariant($item, $variant);
    }

    /**
     * Builds the sale item form the bundle product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    protected function buildFromBundle(SaleItemInterface $item, ProductInterface $product)
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

                // Build the item form the bundle choice's product
                $this->buildFromProduct($childItem, $childItemProduct);
                $childItem->setData(static::BUNDLE_SLOT_ID, $bundleSlotId);

                // Item is immutable
                $childItem->setImmutable(true);

                continue 2;
            }

            // Not found : call prepareItem() first.
            throw new RuntimeException("Bundle slot matching item not found.");
        }

        // Removes miss match items
        /*if ($removeMissMatch) {
            foreach ($item->getChildren() as $childItem) {
                $childProduct = $this->provider->resolve($childItem);
                if (null === $childProduct || !in_array($childProduct->getId(), $bundleProductIds)) {
                    $item->removeChild($childItem);
                }
            }
        }*/
    }

    /**
     * Builds the sale item form the configurable product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    protected function buildFromConfigurable(SaleItemInterface $item, ProductInterface $product)
    {
        ProductTypes::assertConfigurable($product);

        // Remove miss match option
        //$removeMissMatch = $this->doRemoveMissMatch($data);

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
     * Builds the item's options.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    protected function buildItemOptions(SaleItemInterface $item, ProductInterface $product)
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
                $variant =  $this->provider->getProductRepository()->find($variantId);
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
    public function initializeItem(SaleItemInterface $item)
    {
        $product = $this->provider->resolve($item);

        if ($product->getType() === ProductTypes::TYPE_VARIABLE) {
            /** @var ProductInterface $variant */
            $variant = $product->getVariants()->first();
            $item->setData(static::VARIANT_ID, $variant->getId());

        } elseif (in_array($product->getType(), [ProductTypes::TYPE_BUNDLE, ProductTypes::TYPE_CONFIGURABLE])) {
            $this->initializeBundleChildren($item, $product);
        }

        // Initialization is done by the option groups form type.
        //$this->initializeOptionChildren($item);
    }

    /**
     * Initializes the item's children regarding to the product's bundles slots.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     */
    protected function initializeBundleChildren(SaleItemInterface $item, ProductInterface $product)
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

                    // Get/resolve item subject
                    $childProduct = $this->provider->resolve($child);

                    // If invalid choice
                    if (!in_array($childProduct, $choiceProducts)) {
                        // Assign the default product
                        $child->getSubjectIdentity()->clear();
                        $this->provider->assign($child, $defaultChoice->getProduct());

                        // Set quantity
                        $child->setQuantity($defaultChoice->getMinQuantity());
                    }

                    $child->setPosition($bundleSlot->getPosition());

                    // Next bundle slot
                    continue 2;
                }
            }

            // Item not found : create it
            /** @var SaleItemInterface $child */
            $child = $item->createChild();

            $this->provider->assign($child, $defaultChoice->getProduct());

            $child
                ->setQuantity($defaultChoice->getMinQuantity())
                ->setPosition($bundleSlot->getPosition())
                ->setData(static::BUNDLE_SLOT_ID, $bundleSlot->getId());
        }

        // TODO Sort items by position ?
    }

    /**
     * Initializes the item's children regarding to the product's options.
     *
     * @param SaleItemInterface $item
     */
    protected function initializeOptionChildren(SaleItemInterface $item)
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

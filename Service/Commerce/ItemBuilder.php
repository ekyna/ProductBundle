<?php

namespace Ekyna\Bundle\ProductBundle\Service\Commerce;

use Doctrine\Common\Collections\Criteria;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;

/**
 * Class ItemBuilder
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ItemBuilder
{
    const REMOVE_MISS_MATCH = 'remove_miss_match';
    const VARIANT_ID        = 'variant_id';

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
    public function initializeItem(SaleItemInterface $item)
    {
        $product = $this->provider->resolve($item);

        if ($product->getType() === ProductTypes::TYPE_VARIABLE) {
            /** @var ProductInterface $variant */
            $variant = $product->getVariants()->first();
            $item->setSubjectData(static::VARIANT_ID, $variant->getId());

        } elseif (in_array($product->getType(), [ProductTypes::TYPE_BUNDLE, ProductTypes::TYPE_CONFIGURABLE])) {
            $itemClass = get_class($item);

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
                        $bundleSlotId = intval($child->getSubjectData(BundleSlotInterface::BUNDLE_SLOT_ID));
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
                /** @var SaleItemInterface $bundleSlotItem */
                $bundleSlotItem = new $itemClass; // TODO use the SaleFactory ?

                $this->provider->assign($bundleSlotItem, $defaultChoice->getProduct());

                $bundleSlotItem
                    ->setSubjectData(BundleSlotInterface::BUNDLE_SLOT_ID, $bundleSlot->getId())
                    ->setQuantity($defaultChoice->getMinQuantity())
                    ->setPosition($bundleSlot->getPosition());

                $item->addChild($bundleSlotItem);
            }

            // TODO Sort items by position ?
        }
    }

    /**
     * @inheritdoc
     */
    public function buildItem(SaleItemInterface $item, array $data = [])
    {
        // TODO assert ProductInterface
        $product = $this->provider->resolve($item);

        $this->buildItemFromProduct($item, $product, $data);
    }

    /**
     * Builds the sale item from the product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array             $data
     */
    protected function buildItemFromProduct(SaleItemInterface $item, ProductInterface $product, array $data = [])
    {
        switch ($product->getType()) {
            case ProductTypes::TYPE_SIMPLE:
            case ProductTypes::TYPE_VARIANT:
                $this->buildSimpleItem($item, $product, $data);
                break;
            case ProductTypes::TYPE_VARIABLE:
                $this->buildVariableItem($item, $product, $data);
                break;
            case ProductTypes::TYPE_BUNDLE:
                $this->buildBundleItem($item, $product, $data);
                break;
            case ProductTypes::TYPE_CONFIGURABLE:
                $this->buildConfigurableItem($item, $product, $data);
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
     * @param array             $data
     */
    protected function buildSimpleItem(SaleItemInterface $item, ProductInterface $product, array $data = [])
    {
        ProductTypes::assertChildType($product);

        $this->doRemoveMissMatch($data);

        $this->provider->assign($item, $product);

        $item
            ->setSubjectData($data)
            ->setDesignation((string)$product)
            ->setReference($product->getReference())
            ->setNetPrice($product->getNetPrice())
            ->setWeight($product->getWeight())
            ->setTaxGroup($product->getTaxGroup());
    }

    /**
     * Builds the sale item form the variable product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array             $data
     */
    protected function buildVariableItem(SaleItemInterface $item, ProductInterface $product, array $data = [])
    {
        ProductTypes::assertVariable($product);

        $this->doRemoveMissMatch($data);

        $variant = null;

        if (0 < ($variantId = intval($item->getSubjectData(static::VARIANT_ID)))) {
            foreach ($product->getVariants() as $v) {
                if ($variantId == $v->getId()) {
                    $variant = $v;
                    break;
                }
            }
        }

        if (null === $variant) {
            throw new RuntimeException("Failed to resolve variable's selected variant.");
        }

        $this->buildSimpleItem($item, $variant, array_merge($data, [
            static::VARIANT_ID => $variantId,
        ]));
    }

    /**
     * Builds the sale item form the bundle product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array             $data
     */
    protected function buildBundleItem(SaleItemInterface $item, ProductInterface $product, array $data = [])
    {
        ProductTypes::assertBundle($product);

        // Remove miss match option
        $removeMissMatch = $this->doRemoveMissMatch($data);

        $this->provider->assign($item, $product);

        // Bundle root item
        $item
            ->setSubjectData($data)
            ->setDesignation($product->getDesignation())
            ->setReference($product->getReference());

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
                $bundleSlotId = intval($childItem->getSubjectData(BundleSlotInterface::BUNDLE_SLOT_ID));
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
                $this->buildItemFromProduct($childItem, $childItemProduct, [
                    BundleSlotInterface::BUNDLE_SLOT_ID => $bundleSlotId,
                ]);

                // Item is immutable
                $childItem->setImmutable(true);

                continue 2;
            }

            // Not found : call prepareItem() first.
            throw new RuntimeException("Bundle slot matching item not found.");
        }

        // Removes miss match items
        if ($removeMissMatch) {
            foreach ($item->getChildren() as $childItem) {
                $childProduct = $this->provider->resolve($childItem);
                if (null === $childProduct || !in_array($childProduct->getId(), $bundleProductIds)) {
                    $item->removeChild($childItem);
                }
            }
        }
    }

    /**
     * Builds the sale item form the configurable product.
     *
     * @param SaleItemInterface $item
     * @param ProductInterface  $product
     * @param array             $data
     */
    protected function buildConfigurableItem(SaleItemInterface $item, ProductInterface $product, array $data = [])
    {
        ProductTypes::assertConfigurable($product);

        // Remove miss match option
        $removeMissMatch = $this->doRemoveMissMatch($data);

        $this->provider->assign($item, $product);

        // Configurable root item
        $item
            ->setSubjectData($data)
            ->setDesignation($product->getDesignation())
            ->setReference($product->getReference())
            ->setConfigurable(true);

        // Every slot must match a single item
        $bundleSlotIds = [];
        foreach ($product->getBundleSlots() as $bundleSlot) {
            // Find matching item
            foreach ($item->getChildren() as $childItem) {
                $bundleSlotId = intval($childItem->getSubjectData(BundleSlotInterface::BUNDLE_SLOT_ID));
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
                $this->buildItemFromProduct($childItem, $childItemProduct, [
                    BundleSlotInterface::BUNDLE_SLOT_ID => $bundleSlotId,
                ]);

                // Item is immutable
                $childItem->setImmutable(true);

                continue 2;
            }

            // Not found : call prepareItem() first.
            throw new RuntimeException("Bundle slot matching item not found.");
        }

        // Removes miss match items
        if ($removeMissMatch) {
            foreach ($item->getChildren() as $childItem) {
                /** @var ProductInterface $childProduct */
                $childProduct = $this->provider->resolve($childItem);
                if (null === $childProduct) {
                    $item->removeChild($childItem);
                    continue;
                }

                // Find matching slot
                $bundleSlotId = intval($childItem->getSubjectData(BundleSlotInterface::BUNDLE_SLOT_ID));
                $bundleSlots = $product->getBundleSlots()->matching(
                    Criteria::create()->where(Criteria::expr()->eq('id', $bundleSlotId))
                );
                if (1 != $bundleSlots->count()) {
                    $item->removeChild($childItem);
                    continue;
                }

                $slotProductsIds = [];
                $bundleSlot = $bundleSlots->first();
                /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $bundleChoice */
                foreach ($bundleSlot->getChoices() as $bundleChoice) {
                    $slotProductsIds[] = $bundleChoice->getProduct()->getId();
                }

                if (!in_array($childProduct->getId(), $slotProductsIds)) {
                    $item->removeChild($childItem);
                }
            }
        }
    }

    /**
     * Returns whether miss matches should be removed.
     *
     * @param array $data
     *
     * @return bool
     */
    private function doRemoveMissMatch(&$data)
    {
        $removeMissMatch = isset($data[static::REMOVE_MISS_MATCH]) && (bool)$data[static::REMOVE_MISS_MATCH];

        unset($data[static::REMOVE_MISS_MATCH]);

        return $removeMissMatch;
    }
}

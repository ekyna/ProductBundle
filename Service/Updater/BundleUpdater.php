<?php

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;

/**
 * Class BundleUpdater
 * @package Ekyna\Bundle\ProductBundle\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleUpdater extends AbstractUpdater
{
    /**
     * @inheritdoc
     */
    public function updateNetPrice(Model\ProductInterface $bundle)
    {
        Model\ProductTypes::assertBundle($bundle);

        $netPrice = $this->priceCalculator->calculateBundleMinPrice($bundle, false);
        if (is_null($bundle->getNetPrice()) || 0 !== bccomp($bundle->getNetPrice(), $netPrice, 5)) {
            $bundle->setNetPrice($netPrice);

            return true;
        }


        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateMinPrice(Model\ProductInterface $bundle)
    {
        Model\ProductTypes::assertBundle($bundle);

        $minPrice = $this->priceCalculator->calculateBundleMinPrice($bundle);
        if (is_null($bundle->getMinPrice()) || 0 !== bccomp($bundle->getMinPrice(), $minPrice, 5)) {
            $bundle->setMinPrice($minPrice);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateStock(Model\ProductInterface $bundle)
    {
        Model\ProductTypes::assertBundle($bundle);

        $unit = $bundle->getUnit();
        $justInTime = true; $disabled = true; $supplierPreOrder = true;
        $inStock = $virtualStock = $availableStock = $eda = null;

        // TODO Use packaging format

        $bundleSlots = $bundle->getBundleSlots()->getIterator();
        /** @var \Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface $slot */
        if (0 < $bundleSlots->count()) {
            foreach ($bundleSlots as $slot) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $choice */
                $choice = $slot->getChoices()->first();
                $product = $choice->getProduct();

                // Mode
                if ($product->getStockMode() === StockSubjectModes::MODE_DISABLED) {
                    continue;
                }

                // State
                $disabled = false;
                if ($product->getStockMode() !== StockSubjectModes::MODE_JUST_IN_TIME) {
                    $justInTime = false;
                }

                // In stock
                $slotInStock = Units::round($product->getInStock() / $choice->getMinQuantity(), $unit);
                if (null === $inStock || $slotInStock < $inStock) {
                    $inStock = $slotInStock;
                }

                // Available stock
                $slotAvailableStock = Units::round($product->getAvailableStock() / $choice->getMinQuantity(), $unit);
                if (null === $availableStock || $slotAvailableStock < $availableStock) {
                    $availableStock = $slotAvailableStock;
                }

                // Virtual stock
                if (0 < $slotVirtualStock = Units::round($product->getVirtualStock() / $choice->getMinQuantity(), $unit)) {
                    if (null === $virtualStock || $slotVirtualStock <= $virtualStock) {
                        $virtualStock = $slotVirtualStock;
                    }

                    // Estimated date of arrival
                    if (null !== $slotEda = $product->getEstimatedDateOfArrival()) {
                        if (null === $eda || $slotEda > $eda) {
                            $eda = $slotEda;
                        }
                    }
                } else {
                    $virtualStock = 0;
                }

                // Supplier pre order
                if (
                    0 >= $slotAvailableStock &&
                    0 >= $slotVirtualStock &&
                    $product->getStockState() === StockSubjectStates::STATE_OUT_OF_STOCK
                ) {
                    $supplierPreOrder = false;
                }
            }
        }

        if (null === $inStock) $inStock = 0;
        if (null === $availableStock) $availableStock = 0;
        if (null === $virtualStock) $virtualStock = 0;

        if ($disabled) {
            $mode = StockSubjectModes::MODE_DISABLED;
            $state = StockSubjectStates::STATE_IN_STOCK;
        } else {
            $mode = $justInTime ? StockSubjectModes::MODE_JUST_IN_TIME : StockSubjectModes::MODE_AUTO;

            $state = StockSubjectStates::STATE_OUT_OF_STOCK;
            if (0 < $availableStock) {
                $state = StockSubjectStates::STATE_IN_STOCK;
            } elseif ((0 < $virtualStock && null !== $eda) || $supplierPreOrder) {
                $state = StockSubjectStates::STATE_PRE_ORDER;
            }

            // If "Just in time" mode
            if ($mode === StockSubjectModes::MODE_JUST_IN_TIME) {
                // If "out of stock" state
                if ($state === StockSubjectStates::STATE_OUT_OF_STOCK) {
                    // Fallback to "Pre order" state
                    $state = StockSubjectStates::STATE_PRE_ORDER;
                }
                // Else if "pre order" state
                elseif($state === StockSubjectStates::STATE_PRE_ORDER) {
                    // Fallback to "In stock" state
                    $state = StockSubjectStates::STATE_IN_STOCK;
                }
            }
        }

        $changed = false;

        if ($mode !== $bundle->getStockMode()) {
            $bundle->setStockMode($mode);
            $changed = true;
        }
        if ($state != $bundle->getStockState()) {
            $bundle->setStockState($state);
            $changed = true;
        }
        if ($inStock != $bundle->getInStock()) {
            $bundle->setInStock($inStock);
            $changed = true;
        }
        if ($availableStock != $bundle->getAvailableStock()) {
            $bundle->setAvailableStock($availableStock);
            $changed = true;
        }
        if ($virtualStock != $bundle->getVirtualStock()) {
            $bundle->setVirtualStock($virtualStock);
            $changed = true;
        }
        if ($eda != $bundle->getEstimatedDateOfArrival()) {
            $bundle->setEstimatedDateOfArrival($eda);
            $changed = true;
        }

        return $changed;
    }

    /**
     * @inheritdoc
     */
    public function updateAvailability(Model\ProductInterface $bundle)
    {
        Model\ProductTypes::assertBundle($bundle);

        $changed = false;

        $quoteOnly = $endOfLife = false;

        $bundleSlots = $bundle->getBundleSlots()->getIterator();
        /** @var \Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface $slot */
        if (0 < $bundleSlots->count()) {
            foreach ($bundleSlots as $slot) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $choice */
                $choice = $slot->getChoices()->first();
                $product = $choice->getProduct();

                // Quote only
                if ($product->isQuoteOnly()) {
                    $quoteOnly = true;
                }
                // End of life
                if ($product->isEndOfLife()) {
                    $endOfLife = true;
                }
                // Break if both true
                if ($quoteOnly && $endOfLife) {
                    break;
                }
            }
        }
        if ($quoteOnly != $bundle->isQuoteOnly()) {
            $bundle->setQuoteOnly($quoteOnly);
            $changed = true;
        }
        if ($endOfLife != $bundle->isEndOfLife()) {
            $bundle->setEndOfLife($endOfLife);
            $changed = true;
        }

        return $changed;
    }
}

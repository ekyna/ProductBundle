<?php

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;

/**
 * Class ConfigurableUpdater
 * @package Ekyna\Bundle\ProductBundle\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigurableUpdater extends AbstractUpdater
{
    /**
     * @inheritdoc
     */
    public function updateMinPrice(Model\ProductInterface $bundle)
    {
        Model\ProductTypes::assertConfigurable($bundle);

        $minPrice = $this->priceCalculator->calculateConfigurableMinPrice($bundle);

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
        Model\ProductTypes::assertConfigurable($bundle);

        $disabled = true; $justInTime = true; $supplierPreOrder = true;
        $inStock = $virtualStock = $availableStock = $eda = null;

        // TODO Use packaging format

        $bundleSlots = $bundle->getBundleSlots()->getIterator();
        /** @var \Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface $slot */
        if (0 < $bundleSlots->count()) {
            $slotsBestChoices = [];

            // Resolve best choice for each slots
            foreach ($bundleSlots as $slot) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $bestChoice */
                $bestChoice = null;

                if (!$slot->isRequired()) {
                    continue;
                }

                foreach ($slot->getChoices() as $choice) {
                    $product = $choice->getProduct();

                    // Skip disabled product
                    if ($product->getStockMode() === StockSubjectModes::MODE_DISABLED) {
                        continue;
                    }

                    if (null === $bestChoice) {
                        $bestChoice = $choice;
                        continue;
                    }

                    $bestProduct = $bestChoice->getProduct();

                    // Available stock
                    if (0 < $availableStock = $product->getAvailableStock() / $choice->getMinQuantity()) {
                        $bestAvailableStock = $bestProduct->getAvailableStock() / $bestChoice->getMinQuantity();
                        if ($bestAvailableStock < $availableStock) {
                            $bestChoice = $choice;
                            continue;
                        }
                    }

                    // Virtual stock
                    if (0 < $virtualStock = $product->getVirtualStock() / $choice->getMinQuantity()) {
                        $bestVirtualStock = $bestProduct->getVirtualStock() / $bestChoice->getMinQuantity();
                        if ($bestVirtualStock < $virtualStock) {
                            $bestChoice = $choice;
                            continue;
                        }

                        // Estimated date of arrival
                        if (null !== $eda = $product->getEstimatedDateOfArrival()) {
                            $bestEda = $bestProduct->getEstimatedDateOfArrival();
                            if (null === $bestEda || $bestEda > $eda) {
                                $bestChoice = $choice;
                            }
                        }
                    }
                }

                $slotsBestChoices[] = $bestChoice;
            }

            $inStock = $virtualStock = $availableStock = $eda = null;

            // For each slot's best choice
            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $choice */
            foreach ($slotsBestChoices as $choice) {
                if (null === $choice) { // All slot choices have disabled stock mode.
                    continue;
                }

                $product = $choice->getProduct();

                // State
                $disabled = false;
                if ($product->getStockMode() !== StockSubjectModes::MODE_JUST_IN_TIME) {
                    $justInTime = false;
                }

                // In stock
                $slotInStock = $product->getInStock() / $choice->getMinQuantity();
                if (null === $inStock || $slotInStock < $inStock) {
                    $inStock = $slotInStock;
                }

                // Available stock
                $slotAvailableStock = $product->getAvailableStock() / $choice->getMinQuantity();
                if (null === $availableStock || $slotAvailableStock < $availableStock) {
                    $availableStock = $slotAvailableStock;
                }

                // Virtual stock
                $slotVirtualStock = $product->getVirtualStock() / $choice->getMinQuantity();
                if (null === $virtualStock || $slotVirtualStock < $virtualStock) {
                    $virtualStock = $slotVirtualStock;

                    if (null !== $slotEda = $product->getEstimatedDateOfArrival()) {
                        if (null === $eda || $slotEda > $eda) {
                            $eda = $slotEda;
                        }
                    }
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
        if ($state !== $bundle->getStockState()) {
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
        if ($eda !== $bundle->getEstimatedDateOfArrival()) {
            $bundle->setEstimatedDateOfArrival($eda);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Updates the configurable availability.
     *
     * @param Model\ProductInterface $bundle
     *
     * @return bool
     */
    public function updateAvailability(Model\ProductInterface $bundle)
    {
        Model\ProductTypes::assertConfigurable($bundle);

        $changed = false;

        $slotQuoteOnly = [];
        $slotEndOfLife = [];

        $bundleSlots = $bundle->getBundleSlots()->getIterator();
        /** @var \Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface $slot */
        if (0 < $bundleSlots->count()) {
            foreach ($bundleSlots as $slot) {
                // Per slot quote only / end of life
                $quoteOnly = $endOfLife = true;

                foreach ($slot->getChoices() as $choice) {
                    $product = $choice->getProduct();

                    // Quote only
                    if (!$product->isQuoteOnly()) {
                        $quoteOnly = false;
                    }
                    // End of life
                    if (!$product->isEndOfLife()) {
                        $endOfLife = false;
                    }
                    // Break if both false
                    if (!$quoteOnly && !$endOfLife) {
                        break;
                    }
                }

                $slotQuoteOnly[] = $quoteOnly;
                $slotEndOfLife[] = $endOfLife;
            }
        }

        // Is all slots "quote only" ?
        $quoteOnly = array_product($slotQuoteOnly);
        // Is all slots "end of life" ?
        $endOfLife = array_product($slotEndOfLife);

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

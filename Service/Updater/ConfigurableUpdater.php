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
class ConfigurableUpdater
{
    /**
     * Updates the configurable stock data.
     *
     * @param Model\ProductInterface $bundle
     *
     * @return bool Whether or not the bundle has been changed.
     */
    public function updateStock(Model\ProductInterface $bundle)
    {
        Model\ProductTypes::assertConfigurable($bundle);

        $justInTime = true;
        $inStock = $virtualStock = $availableStock = $eda = null;

        $bundleSlots = $bundle->getBundleSlots()->getIterator();
        /** @var \Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface $slot */
        if (0 < $bundleSlots->count()) {
            $slotsBestChoices = [];

            // Resolve best choice for each slots
            foreach ($bundleSlots as $slot) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $bestChoice */
                $bestChoice = null;

                foreach ($slot->getChoices() as $choice) {
                    $product = $choice->getProduct();

                    if (null === $bestChoice || $product->getStockMode() === StockSubjectModes::MODE_JUST_IN_TIME) {
                        $bestChoice = $choice;
                        continue;
                    }

                    $bestProduct = $bestChoice->getProduct();

                    // In stock
                    // In stock can't be used to resolve best slot choice
                    /*if (0 < $inStock = $product->getInStock() / $choice->getMinQuantity()) {
                        $bestInStock = $bestProduct->getInStock() / $bestChoice->getMinQuantity();
                        if ($bestInStock < $inStock) {
                            $bestChoice = $choice;
                            continue;
                        }
                    }*/

                    // Available stock (TODO check)
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

            // For each slot's best choice
            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $choice */
            foreach ($slotsBestChoices as $choice) {
                $product = $choice->getProduct();

                // State
                if ($product->getStockMode() != StockSubjectModes::MODE_JUST_IN_TIME) {
                    $justInTime = false;
                    if ($product->getStockMode() == StockSubjectModes::MODE_DISABLED) {
                        continue;
                    }
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
                }
                if (0 < $slotVirtualStock && null !== $slotEda = $product->getEstimatedDateOfArrival()) {
                    if (null === $eda || $slotEda > $eda) {
                        $eda = $slotEda;
                    }
                }
            }
        }

        $changed = false;

        $state = StockSubjectStates::STATE_OUT_OF_STOCK;
        if ($justInTime || 0 < $inStock) {
            $state = StockSubjectStates::STATE_IN_STOCK;
        } elseif (0 < $virtualStock) {
            $state = StockSubjectStates::STATE_PRE_ORDER;
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
}

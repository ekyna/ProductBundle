<?php

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectStates;

/**
 * Class BundleUpdater
 * @package Ekyna\Bundle\ProductBundle\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleUpdater
{
    /**
     * Updates the bundle stock data.
     *
     * @param Model\ProductInterface $bundle
     *
     * @return bool Whether or not the bundle has been changed.
     */
    public function updateStock(Model\ProductInterface $bundle)
    {
        Model\ProductTypes::assertBundle($bundle);

        $justInTime = true;
        $inStock = $virtualStock = $availableStock = $eda = null;

        $bundleSlots = $bundle->getBundleSlots()->getIterator();
        /** @var \Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface $slot */
        if (0 < $bundleSlots->count()) {
            foreach ($bundleSlots as $slot) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $choice */
                $choice = $slot->getChoices()->first();
                $product = $choice->getProduct();

                // State
                if ($product->getStockMode() != StockSubjectModes::MODE_JUST_IN_TIME) {
                    $justInTime = false;
                    if (
                        !Model\ProductTypes::isBundled($product->getType()) &&
                        $product->getStockMode() === StockSubjectModes::MODE_INHERITED

                    ) {
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
        if (0 < $availableStock) {
            $state = StockSubjectStates::STATE_IN_STOCK;
        } elseif (0 < $virtualStock || $justInTime) {
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

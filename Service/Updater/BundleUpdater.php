<?php

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Stock\Model\StockModes;

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

        if ($bundle->getStockMode() != StockModes::MODE_ENABLED) {
            return false;
        }

        $eda = null; $inStock = $orderedStock = 0;

        $bundleSlots = $bundle->getBundleSlots()->getIterator();
        /** @var \Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface $slot */
        if (0 < $bundleSlots->count()) {
            foreach ($bundleSlots as $slot) {
                /** @var \Ekyna\Bundle\ProductBundle\Model\BundleChoiceInterface $choice */
                $choice = $slot->getChoices()->first();
                $product = $choice->getProduct();

                if ($slotInStock = $product->getInStock() / $choice->getMinQuantity()) {
                    if (0 == $inStock || $slotInStock < $inStock) {
                        $inStock = $slotInStock;
                    }
                }

                if ($slotOrderedStock = $product->getOrderedStock() / $choice->getMinQuantity()) {
                    if (0 == $orderedStock || $slotOrderedStock < $orderedStock) {
                        $orderedStock = $slotOrderedStock;

                        if (null !== $slotEda = $product->getEstimatedDateOfArrival()) {
                            if (null === $eda || $slotEda > $eda) {
                                $eda = $slotEda;
                            }
                        }
                    }
                }
            }
        }

        $changed = false;

        if ($inStock != $bundle->getInStock()) {
            $bundle->setInStock($inStock);
            $changed = true;
        }
        if ($orderedStock != $bundle->getOrderedStock()) {
            $bundle->setOrderedStock($orderedStock);
            $changed = true;
        }
        if ($eda != $bundle->getEstimatedDateOfArrival()) {
            $bundle->setEstimatedDateOfArrival($eda);
            $changed = true;
        }

        return $changed;
    }
}

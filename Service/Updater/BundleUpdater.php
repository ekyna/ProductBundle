<?php

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use Ekyna\Bundle\ProductBundle\Model;

/**
 * Class BundleUpdater
 * @package Ekyna\Bundle\ProductBundle\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleUpdater extends AbstractUpdater
{
    /**
     * Updates the bundle net price.
     *
     * @param Model\ProductInterface $bundle
     *
     * @return bool
     */
    public function updateNetPrice(Model\ProductInterface $bundle): bool
    {
        Model\ProductTypes::assertBundle($bundle);

        $netPrice = $this->priceCalculator->calculateBundleMinPrice($bundle, true);
        if (is_null($bundle->getNetPrice()) || 0 !== bccomp($bundle->getNetPrice(), $netPrice, 5)) {
            $bundle->setNetPrice($netPrice);

            return true;
        }


        return false;
    }

    /**
     * Updates the bundle min price.
     *
     * @param Model\ProductInterface $bundle
     *
     * @return bool
     */
    public function updateMinPrice(Model\ProductInterface $bundle): bool
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
     * Updates the bundle availability.
     *
     * @param Model\ProductInterface $bundle
     *
     * @return bool
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

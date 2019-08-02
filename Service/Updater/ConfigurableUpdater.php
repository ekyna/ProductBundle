<?php

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use Ekyna\Bundle\ProductBundle\Model;

/**
 * Class ConfigurableUpdater
 * @package Ekyna\Bundle\ProductBundle\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigurableUpdater extends AbstractUpdater
{
    /**
     * Updates the configurable min price.
     *
     * @param Model\ProductInterface $bundle
     *
     * @return bool
     */
    public function updateMinPrice(Model\ProductInterface $bundle): bool
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
     * Updates the configurable availability.
     *
     * @param Model\ProductInterface $bundle
     *
     * @return bool
     */
    public function updateAvailability(Model\ProductInterface $bundle): bool
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

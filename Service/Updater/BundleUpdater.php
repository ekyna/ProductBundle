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
}

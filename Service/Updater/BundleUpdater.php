<?php

declare(strict_types=1);

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
     */
    public function updateNetPrice(Model\ProductInterface $bundle): bool
    {
        Model\ProductTypes::assertBundle($bundle);

        $netPrice = $this->priceCalculator->calculateBundleMinPrice($bundle, true);
        if (!$bundle->getNetPrice()->equals($netPrice)) {
            $bundle->setNetPrice($netPrice);

            return true;
        }


        return false;
    }

    /**
     * Updates the bundle min price.
     */
    public function updateMinPrice(Model\ProductInterface $bundle): bool
    {
        Model\ProductTypes::assertBundle($bundle);

        $minPrice = $this->priceCalculator->calculateBundleMinPrice($bundle);
        if (!$bundle->getMinPrice()->equals($minPrice)) {
            $bundle->setMinPrice($minPrice);

            return true;
        }

        return false;
    }
}

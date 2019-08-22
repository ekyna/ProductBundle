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
}

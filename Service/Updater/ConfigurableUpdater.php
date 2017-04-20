<?php

declare(strict_types=1);

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
     */
    public function updateMinPrice(Model\ProductInterface $bundle): bool
    {
        Model\ProductTypes::assertConfigurable($bundle);

        $minPrice = $this->priceCalculator->calculateConfigurableMinPrice($bundle);

        if (!$bundle->getMinPrice()->equals($minPrice)) {
            $bundle->setMinPrice($minPrice);

            return true;
        }

        return false;
    }
}

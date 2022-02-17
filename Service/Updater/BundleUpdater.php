<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Commerce\Common\Util\DateUtil;

/**
 * Class BundleUpdater
 * @package Ekyna\Bundle\ProductBundle\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleUpdater extends AbstractUpdater
{
    /**
     * Updates bundle's net price.
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
     * Updates bundle's min price.
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

    /**
     * Updates bundle's 'released at' date.
     */
    public function updateReleasedAt(Model\ProductInterface $bundle): bool
    {
        Model\ProductTypes::assertBundle($bundle);

        $releasedAt = null;
        foreach ($bundle->getBundleSlots() as $slot) {
            /** @var Model\BundleChoiceInterface $choice */
            $choice = $slot->getChoices()->first();

            if (null === $childReleasedAt = $choice->getProduct()->getReleasedAt()) {
                continue;
            }

            if (null === $releasedAt || $releasedAt < $childReleasedAt) {
                $releasedAt = $childReleasedAt;
            }
        }

        if (DateUtil::equals($bundle->getReleasedAt(), $releasedAt)) {
            return false;
        }

        $bundle->setReleasedAt($releasedAt);

        return true;
    }
}

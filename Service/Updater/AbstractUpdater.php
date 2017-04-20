<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Updater;

use Ekyna\Bundle\ProductBundle\Service\Pricing\PriceCalculator;

/**
 * Class AbstractUpdater
 * @package Ekyna\Bundle\ProductBundle\Service\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractUpdater
{
    protected PriceCalculator $priceCalculator;

    public function __construct(PriceCalculator $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }
}

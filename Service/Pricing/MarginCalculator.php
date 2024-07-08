<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Pricing;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Component\Commerce\Common\Model\Margin;

/**
 * Class MarginCalculator
 * @package Ekyna\Bundle\ProductBundle\Service\Pricing
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MarginCalculator
{
    public function __construct(
        private readonly PurchaseCostCalculator $costCalculator,
    ) {
    }

    public function calculateMargin(ProductInterface $product): Margin
    {

    }
}

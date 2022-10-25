<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Commerce\Report\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Margin;

/**
 * Class ProductData
 * @package Ekyna\Bundle\ProductBundle\Service\Commerce\Report\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ProductData
{
    public function __construct(
        public readonly Margin $grossMargin,
        public readonly Margin $commercialMargin,
        public Decimal         $quantity = new Decimal(0)
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Inventory;

use Decimal\Decimal;
use Ekyna\Bundle\ProductBundle\Entity\InventoryProduct;
use Ekyna\Bundle\ProductBundle\Exception\UnexpectedValueException;

/**
 * Class InventoryCalculator
 * @package Ekyna\Bundle\ProductBundle\Service\Inventory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InventoryCalculator
{
    public function calculateQuantityToApply(InventoryProduct $product): Decimal
    {
        if (null === $product->getRealStock()) {
            throw new UnexpectedValueException('Expected inventory product with defined real stock.');
        }

        return $product->getRealStock()
            - $product->getInitialStock()
            - ($product->getAppliedStock() ?? new Decimal(0));
    }
}

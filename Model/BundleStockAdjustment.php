<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Model;

use Decimal\Decimal;

/**
 * Class BundleStockAdjustment
 * @package Ekyna\Bundle\ProductBundle\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BundleStockAdjustment
{
    public Decimal $quantity;
    public string  $reason;
    public ?string $note = null;

    public function __construct(
        public readonly ProductInterface $bundle
    ) {

    }
}

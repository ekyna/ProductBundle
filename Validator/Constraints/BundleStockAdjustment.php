<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class BundleStockAdjustment
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BundleStockAdjustment extends Constraint
{
    public function getTargets(): string|array
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}

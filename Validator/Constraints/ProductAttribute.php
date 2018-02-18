<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class ProductAttribute
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductAttribute extends Constraint
{
    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

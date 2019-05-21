<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class BundleRule
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleRule extends Constraint
{
    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

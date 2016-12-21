<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Pricing
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Pricing extends Constraint
{
    public $already_exists = 'ekyna_product.pricing.already_exists';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

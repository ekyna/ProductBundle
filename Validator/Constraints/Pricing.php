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
    public $brands_must_be_empty = 'ekyna_product.pricing.brands_must_be_empty';
    public $at_least_one_brand   = 'ekyna_product.pricing.at_least_one_brand';

    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

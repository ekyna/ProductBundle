<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class ProductType
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ProductType extends Constraint
{
    public $message = 'ekyna_product.product.invalid_type';
}

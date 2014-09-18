<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class ValidProductType
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ValidProductType extends Constraint
{
    public $message = 'ekyna_product.product.invalid_type';
}

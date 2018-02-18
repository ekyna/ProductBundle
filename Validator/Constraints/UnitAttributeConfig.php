<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class UnitAttributeConfig
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnitAttributeConfig extends Constraint
{
    public $suffix_is_mandatory = 'ekyna_product.attribute.config.suffix_is_mandatory';
}
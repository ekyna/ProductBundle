<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class BundleRules
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleRules extends Constraint
{
    public $duplicate_rule_type = 'ekyna_product.bundle_slot.duplicate_rule_type';
}

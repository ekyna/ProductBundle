<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class BundleSlot
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlot extends Constraint
{
    public $too_many_choices    = 'ekyna_product.bundle_slot.too_many_choices';
    public $required_with_rules = 'ekyna_product.bundle_slot.required_with_rules';

    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

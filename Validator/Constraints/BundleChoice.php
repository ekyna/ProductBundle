<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class BundleChoice
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoice extends Constraint
{
    public $invalid_quantity_range   = 'ekyna_product.bundle_choice.invalid_quantity_range';
    public $rules_should_be_empty    = 'ekyna_product.bundle_choice.rules_should_be_empty';
    public $recursive_choice         = 'ekyna_product.bundle_choice.recursive_choice';
    public $forbidden_price_override = 'ekyna_product.bundle_choice.forbidden_price_override';

    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

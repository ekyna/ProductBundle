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
    public $invalidQuantityRange = 'ekyna_product.bundle_choice.invalid_quantity_range';
    public $rulesShouldBeEmpty = 'ekyna_product.bundle_choice.rules_should_be_empty';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

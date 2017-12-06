<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Product
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Product extends Constraint
{
    public $configurable_must_be_visible = 'ekyna_product.product.configurable_must_be_visible';
    public $bundle_must_be_visible       = 'ekyna_product.product.bundle_must_be_visible';
    public $child_must_not_be_visible    = 'ekyna_product.product.child_must_not_be_visible';
    public $child_must_be_visible        = 'ekyna_product.product.child_must_be_visible';
    public $parent_tax_group_integrity   = 'ekyna_product.product.parent_tax_group_integrity';
    public $tax_group_integrity          = 'ekyna_product.product.tax_group_integrity';

    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}

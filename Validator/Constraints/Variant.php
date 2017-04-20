<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class Variant
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Variant extends Constraint
{
    public $slotAttributeIsMandatory = 'ekyna_product.product.slot_attribute_is_mandatory';
    public $slotHasTooManyAttributes = 'ekyna_product.product.slot_has_too_many_attributes';
    public $unexpectedAttribute      = 'ekyna_product.product.unexpected_attribute';
    public $variantIsNotUnique       = 'ekyna_product.product.variant_is_not_unique';
    public $designationNeeded        = 'ekyna_product.product.designation_needed';
    public $translationTitleNeeded   = 'ekyna_product.product.translation_title_needed';

    /**
     * @inheritDoc
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Component\Sale\Product\ProductTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ValidProductTypeValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ValidProductTypeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!ProductTypes::isValid($value)) {
            $this->context->addViolation($constraint->message, array('%type%' => $value));
        }
    }
}

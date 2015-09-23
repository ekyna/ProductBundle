<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Component\Sale\Product\ProductTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ProductTypeValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ProductTypeValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ProductType) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\ProductType');
        }

        if (!ProductTypes::isValid($value)) {
            $this->context->addViolation($constraint->message, ['%type%' => $value]);
        }
    }
}

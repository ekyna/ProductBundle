<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ProductTypeValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductTypeValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($typeOrProduct, Constraint $constraint)
    {
        if (!$constraint instanceof ProductType) {
            throw new UnexpectedTypeException($constraint, ProductType::class);
        }

        if (is_null($typeOrProduct)) {
            return;
        }

        $type = $typeOrProduct instanceof ProductInterface ? $typeOrProduct->getType() : $typeOrProduct;

        /* @var string $type */
        /* @var ProductType $constraint */

        /* TODO insert expected types (translated) in error message */

        if (!in_array($type, $constraint->types)) {
            $this->context
                ->buildViolation($constraint->invalidType)
                ->addViolation();
        }
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class ProductTypeValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductTypeValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($typeOrProduct, Constraint $constraint)
    {
        if (!$constraint instanceof ProductType) {
            throw new InvalidArgumentException("Expected instance of ProductType (validation constraint)");
        }

        if ($typeOrProduct instanceof ProductInterface) {
            $typeOrProduct = $typeOrProduct->getType();
        }

        /* @var string $type */
        /* @var ProductType $constraint */

        /* TODO insert expected types (translated) in error message */

        if (!in_array($typeOrProduct, $constraint->types)) {
            $this->context
                ->buildViolation($constraint->invalidProductType)
                ->addViolation();
        }
    }
}

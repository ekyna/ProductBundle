<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class PricingValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($pricing, Constraint $constraint)
    {
        if (!$pricing instanceof Model\PricingInterface) {
            throw new InvalidArgumentException("Expected instance of PricingInterface");
        }
        if (!$constraint instanceof Pricing) {
            throw new InvalidArgumentException("Expected instance of Pricing (validation constraint)");
        }

        // Single product case
        if (null !== $pricing->getProduct()) {
            if (0 < $pricing->getBrands()->count()) {
                $this
                    ->context
                    ->buildViolation($constraint->brands_must_be_empty)
                    ->atPath('brands')
                    ->addViolation();
            }

            return;
        }

        // Multiple product case
        if (0 === $pricing->getBrands()->count()) {
            $this
                ->context
                ->buildViolation($constraint->at_least_one_brand)
                ->atPath('brands')
                ->addViolation();
        }
    }
}

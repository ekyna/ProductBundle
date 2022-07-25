<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class PricingValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PricingValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof Model\PricingInterface) {
            throw new UnexpectedTypeException($value, Model\PricingInterface::class);
        }
        if (!$constraint instanceof Pricing) {
            throw new UnexpectedTypeException($constraint, Pricing::class);
        }

        // Single product case
        if (null !== $value->getProduct()) {
            if (!$value->getPricingGroups()->isEmpty()) {
                $this
                    ->context
                    ->buildViolation($constraint->pricing_groups_must_be_empty)
                    ->atPath('pricingGroups')
                    ->addViolation();
            }

            if (!$value->getBrands()->isEmpty()) {
                $this
                    ->context
                    ->buildViolation($constraint->brands_must_be_empty)
                    ->atPath('brands')
                    ->addViolation();
            }

            return;
        }

        // Multiple product case
        if (!$value->getPricingGroups()->isEmpty()) {
            return;
        }
        if (!$value->getBrands()->isEmpty()) {
            return;
        }

        $this
            ->context
            ->buildViolation($constraint->at_least_one_pricing_group_or_brand)
            ->addViolation();
    }
}

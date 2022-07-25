<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class SpecialOfferValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SpecialOfferValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof SpecialOfferInterface) {
            throw new UnexpectedTypeException($value, SpecialOfferInterface::class);
        }
        if (!$constraint instanceof SpecialOffer) {
            throw new UnexpectedTypeException($constraint, SpecialOffer::class);
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

            if (!$value->getProducts()->isEmpty()) {
                $this
                    ->context
                    ->buildViolation($constraint->products_must_be_empty)
                    ->atPath('products')
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
        if (!$value->getProducts()->isEmpty()) {
            return;
        }

        $this
            ->context
            ->buildViolation($constraint->at_least_one_pricing_group_or_brand_or_product)
            ->addViolation();
    }
}

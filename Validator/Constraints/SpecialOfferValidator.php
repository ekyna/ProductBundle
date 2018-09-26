<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model\SpecialOfferInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class SpecialOfferValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SpecialOfferValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($specialOffer, Constraint $constraint)
    {
        if (!$specialOffer instanceof SpecialOfferInterface) {
            throw new InvalidArgumentException("Expected instance of " . SpecialOfferInterface::class);
        }
        if (!$constraint instanceof SpecialOffer) {
            throw new InvalidArgumentException("Expected instance of " . SpecialOffer::class);
        }

        // Single product case
        if (null !== $specialOffer->getProduct()) {
            if (0 < $specialOffer->getProducts()->count() || 0 < $specialOffer->getBrands()->count()) {
                $this
                    ->context
                    ->buildViolation($constraint->products_and_brands_must_be_empty)
                    ->addViolation();
            }

            return;
        }

        // Multiple product case
        if (0 === $specialOffer->getProducts()->count() && 0 === $specialOffer->getBrands()->count()) {
            $this
                ->context
                ->buildViolation($constraint->at_least_one_brand_or_product)
                ->addViolation();
        }
    }
}

<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Entity\SpecialOffer as Entity;
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
        if (!$specialOffer instanceof Entity) {
            throw new InvalidArgumentException("Expected instance of " . Entity::class);
        }
        if (!$constraint instanceof SpecialOffer) {
            throw new InvalidArgumentException("Expected instance of " . SpecialOffer::class);
        }

        if (0 === $specialOffer->getProducts()->count() && 0 === $specialOffer->getBrands()->count()) {
            $this
                ->context
                ->buildViolation($constraint->at_least_one_brand_or_product)
                ->addViolation();
        }
    }
}

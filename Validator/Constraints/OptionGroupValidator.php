<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class OptionGroupValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionGroupValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($optionGroup, Constraint $constraint)
    {
        if (!$optionGroup instanceof Model\OptionGroupInterface) {
            throw new InvalidArgumentException("Expected instance of OptionGroupInterface");
        }
        if (!$constraint instanceof OptionGroup) {
            throw new InvalidArgumentException("Expected instance of OptionGroup (validation constraint)");
        }

        /* @var Model\OptionGroupInterface $optionGroup */
        /* @var OptionGroup $constraint */

        // Asserts that the product has 'simple' or 'variant' type
        if (null === $product = $optionGroup->getProduct()) {
            throw new RuntimeException("Option group's product must be defined.");
        }

        $validTypes = [
            Model\ProductTypes::TYPE_SIMPLE,
            Model\ProductTypes::TYPE_VARIANT,
        ];

        if (!in_array($product->getType(), $validTypes)) {
            $this->context
                ->buildViolation($constraint->unsupportedProductType)
                ->atPath('product')
                ->addViolation();
        }
    }
}

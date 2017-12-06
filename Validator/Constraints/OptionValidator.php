<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class OptionValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($option, Constraint $constraint)
    {
        if (!$option instanceof Model\OptionInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\OptionInterface::class);
        }
        if (!$constraint instanceof Option) {
            throw new InvalidArgumentException("Expected instance of " . Option::class);
        }

        /* @var Model\OptionInterface $option */
        /* @var Option $constraint */

        $product = $option->getProduct();

        if ($product === $option->getGroup()->getProduct()) {
            $this->context
                ->buildViolation($constraint->recursive_choice)
                ->atPath('product')
                ->addViolation();

            return;
        }

        if (null !== $product && !$product->isVisible()) {
            $this->context
                ->buildViolation($constraint->product_must_be_visible)
                ->atPath('product')
                ->addViolation();
        }
    }
}

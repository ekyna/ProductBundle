<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class OptionValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OptionValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Model\OptionInterface) {
            throw new UnexpectedTypeException($value, Model\OptionInterface::class);
        }
        if (!$constraint instanceof Option) {
            throw new UnexpectedTypeException($constraint, Option::class);
        }

        if ($value->getProduct() !== $value->getGroup()->getProduct()) {
            return;
        }

        $this->context
            ->buildViolation($constraint->recursive_choice)
            ->atPath('product')
            ->addViolation();
    }
}

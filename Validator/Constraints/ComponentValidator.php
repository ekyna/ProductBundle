<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model\ComponentInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ComponentValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ComponentValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof ComponentInterface) {
            throw new UnexpectedTypeException($value, ComponentInterface::class);
        }
        if (!$constraint instanceof Component) {
            throw new UnexpectedTypeException($constraint, Component::class);
        }

        if ($value->getChild()->getTaxGroup() !== $value->getParent()->getTaxGroup()) {
            $this->context
                ->buildViolation('ekyna_product.component.tax_group_integrity')
                ->atPath('child')
                ->addViolation();
        }
    }
}

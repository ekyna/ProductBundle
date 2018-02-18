<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\Units;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class UnitAttributeConfigValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnitAttributeConfigValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        if (!$constraint instanceof UnitAttributeConfig) {
            throw new InvalidArgumentException("Expected instance of " . UnitAttributeConfig::class);
        }

        if ($value['unit'] === Units::PIECE && empty($value['suffix'])) {
            $this
                ->context
                ->buildViolation($constraint->suffix_is_mandatory)
                ->atPath('[suffix]')
                ->addViolation();
        }
    }
}
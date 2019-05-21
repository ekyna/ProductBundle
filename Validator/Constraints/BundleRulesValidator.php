<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Model\BundleRuleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class BundleRulesValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleRulesValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof BundleRules) {
            throw new UnexpectedTypeException($constraint, BundleRules::class);
        }

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        if (!is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        $types = [];
        foreach ($value as $index => $rule) {
            if (!$rule instanceof BundleRuleInterface) {
                throw new UnexpectedTypeException($rule, BundleRuleInterface::class);
            }

            if (in_array($rule->getType(), $types, true)) {
                $this
                    ->context
                    ->buildViolation($constraint->duplicate_rule_type)
                    ->atPath("[$index].type")
                    ->addViolation();

                return;
            }

            $types[] = $rule->getType();
        }
    }
}

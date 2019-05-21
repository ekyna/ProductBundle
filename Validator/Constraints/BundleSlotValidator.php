<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class BundleSlotValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleSlotValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($bundleSlot, Constraint $constraint)
    {
        if (!$bundleSlot instanceof Model\BundleSlotInterface) {
            throw new InvalidArgumentException("Expected instance of BundleSlotInterface");
        }
        if (!$constraint instanceof BundleSlot) {
            throw new InvalidArgumentException("Expected instance of BundleSlot (validation constraint)");
        }

        /* @var Model\BundleSlotInterface $bundleSlot */
        /* @var BundleSlot $constraint */

        // Only for 'configurable' product type.

        if ($bundleSlot->getBundle()->getType() === Model\ProductTypes::TYPE_CONFIGURABLE) {
            if ($bundleSlot->isRequired() && 0 < $bundleSlot->getRules()->count()) {
                $this->context
                    ->buildViolation($constraint->required_with_rules)
                    ->atPath('required')
                    ->addViolation();
            }

            return;
        }

        // Only for 'bundle' product type.

        // Asserts that one and only one choice is configured.
        if (1 != $bundleSlot->getChoices()->count()) {
            $this->context
                ->buildViolation($constraint->too_many_choices)
                ->atPath('choices')
                ->addViolation();
        }
    }
}

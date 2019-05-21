<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model\BundleChoiceRuleInterface;
use Ekyna\Bundle\ProductBundle\Model\BundleRuleTypes;
use Ekyna\Bundle\ProductBundle\Model\BundleSlotRuleInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class BundleRuleValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleRuleValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($rule, Constraint $constraint)
    {
        if ($rule instanceof BundleSlotRuleInterface) {
            $bundle = $rule->getSlot()->getBundle();
        } elseif ($rule instanceof BundleChoiceRuleInterface) {
            $bundle = $rule->getChoice()->getSlot()->getBundle();
        } else {
            throw new UnexpectedTypeException($rule, sprintf(
                "%s or %s",
                BundleSlotRuleInterface::class,
                BundleChoiceRuleInterface::class
            ));
        }

        if (!$constraint instanceof BundleRule) {
            throw new UnexpectedTypeException($constraint, BundleRule::class);
        }

        // Skip for non configurable bundle
        if ($bundle->getType() === ProductTypes::TYPE_BUNDLE) {
            return;
        }

        $slotNums = [];
        $slotUniqueness = in_array($rule->getType(), BundleRuleTypes::getIfAllTypes(), true);

        foreach ($rule->getConditions() as $index => $condition) {
            $slotNum = isset($condition['slot']) ? intval($condition['slot']) : -1;
            /** @var \Ekyna\Bundle\ProductBundle\Model\BundleSlotInterface $slot */
            if ((0 > $slotNum) || (!$slot = $bundle->getBundleSlots()->get($slotNum))) {
                $this
                    ->context
                    ->buildViolation('Unknown slot.')
                    ->atPath("conditions[$index].slot")
                    ->addViolation();

                continue;
            }

            if ($slotUniqueness) {
                if (in_array($slotNum, $slotNums, true)) {
                    $this
                        ->context
                        ->buildViolation("Slots must be unique for '* if all' type.")
                        ->atPath("conditions[$index].slot")
                        ->addViolation();
                } else {
                    $slotNums[] = $slotNum;
                }
            }

            $choiceNum = isset($condition['choice']) ? intval($condition['choice']) : -1;
            if ((0 <= $choiceNum) && (!$choice = $slot->getChoices()->get($choiceNum))) {
                $this
                    ->context
                    ->buildViolation('Unknown choice.')
                    ->atPath("conditions[$index].choice")
                    ->addViolation();
            }
        }
    }
}

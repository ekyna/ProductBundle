<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class BundleChoiceValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BundleChoiceValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($bundleChoice, Constraint $constraint)
    {
        if (!$bundleChoice instanceof Model\BundleChoiceInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\BundleChoiceInterface::class);
        }
        if (!$constraint instanceof BundleChoice) {
            throw new InvalidArgumentException("Expected instance of " . BundleChoice::class);
        }

        /* @var Model\BundleChoiceInterface $bundleChoice */
        /* @var BundleChoice $constraint */

        $parent = $bundleChoice->getSlot()->getBundle();
        $product = $bundleChoice->getProduct();

        // Disallow recursion
        if ($product && $product === $parent) {
            $this->context
                ->buildViolation($constraint->recursive_choice)
                ->atPath('product')
                ->addViolation();
        }

        if (!$parent->isVisible() && $product->isVisible()) {
            $this->context
                ->buildViolation($constraint->visibility_integrity)
                ->atPath('product')
                ->addViolation();
        }

        // Private product choice must have the same tax group as the parent's one
        if (!$product->isVisible() && ($product->getTaxGroup() !== $parent->getTaxGroup())) {
            $this->context
                ->buildViolation($constraint->tax_group_integrity)
                ->atPath('product')
                ->addViolation();
        }

        // Only for 'configurable' product type
        if ($parent->getType() === Model\ProductTypes::TYPE_CONFIGURABLE) {
            // Configurable product must have public children
            if (!$product->isVisible()) {
                $this->context
                    ->buildViolation($constraint->must_be_visible)
                    ->atPath('product')
                    ->addViolation();
            }
            // Asserts that the maximum quantity is greater than the minimum quantity
            if ($bundleChoice->getMinQuantity() > $bundleChoice->getMaxQuantity()) {
                $this->context
                    ->buildViolation($constraint->invalid_quantity_range)
                    ->atPath('maxQuantity')
                    ->addViolation();
            }

            return;
        }

        // Only for 'bundle' product type

        // Asserts that no rule is configured
        if (0 < $bundleChoice->getRules()->count()) {
            $this->context
                ->buildViolation($constraint->rules_should_be_empty)
                ->atPath('rules')
                ->addViolation();
        }
    }
}

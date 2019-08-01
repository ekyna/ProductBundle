<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\ProductBundle\Service\Commerce\ItemBuilder;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class SaleItemConfigurableValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemConfigurableValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($item, Constraint $constraint)
    {
        if (!$item instanceof Collection) {
            throw new UnexpectedTypeException($item, Collection::class);
        }
        if (!$constraint instanceof SaleItemConfigurable) {
            throw new UnexpectedTypeException($item, SaleItemConfigurable::class);
        }

        if (!$this->hasOneChoice($item)) {
            $this
                ->context
                ->buildViolation('ekyna_product.sale_item.at_least_one_choice_required')
                ->addViolation();
        }
    }

    private function hasOneChoice(Collection $collection): bool
    {
        /** @var \Ekyna\Component\Commerce\Common\Model\SaleItemInterface $child */
        foreach ($collection->getIterator() as $child) {
            if ($child->hasData(ItemBuilder::OPTION_GROUP_ID)) {
                continue;
            }

            if ($child->hasData(ItemBuilder::BUNDLE_SLOT_ID)) {
                return true;
            }
        }

        return false;
    }
}

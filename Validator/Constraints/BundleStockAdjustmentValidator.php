<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model\BundleStockAdjustment as Adjustment;
use Ekyna\Bundle\ProductBundle\Service\Stock\BundleStockAdjuster;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentReasons;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class BundleStockAdjustmentValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BundleStockAdjustmentValidator extends ConstraintValidator
{
    public function __construct(
        private readonly BundleStockAdjuster $adjuster
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!$value instanceof Adjustment) {
            throw new UnexpectedTypeException($value, Adjustment::class);
        }
        if (!$constraint instanceof BundleStockAdjustment) {
            throw new UnexpectedTypeException($constraint, BundleStockAdjustment::class);
        }

        if (!StockAdjustmentReasons::isDebitReason($value->reason)) {
            return;
        }

        $min = $this->adjuster->calculateMaxDebit($value);

        if ($min >= $value->quantity) {
            return;
        }

        $this->context
            ->buildViolation((new LessThan($min))->message, [
                '{{ compared_value }}' => $min->toFixed(),
            ])
            ->atPath('quantity')
            ->addViolation();
    }
}

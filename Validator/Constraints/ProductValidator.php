<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ProductValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof Model\ProductInterface) {
            throw new UnexpectedTypeException($value, Model\ProductInterface::class);
        }
        if (!$constraint instanceof Product) {
            throw new UnexpectedTypeException($constraint, Product::class);
        }

        $this->validateReference($value);

        if ($value->getType() === Model\ProductTypes::TYPE_VARIANT) {
            $this->validateVariantDesignation($value);
        } else {
            $this->validateDesignation($value);
        }

        // TODO unique option groups by name
    }

    /**
     * Validates the product reference uniqueness.
     *
     * @param Model\ProductInterface $product
     */
    private function validateReference(Model\ProductInterface $product): void
    {
        if (in_array($product->getReference(), $product->getReferenceAliases(), true)) {
            $this
                ->context
                ->buildViolation('Can`t use a previous reference')
                ->atPath('reference')
                ->addViolation();

            return;
        }

        if (null === $duplicate = $this->repository->findDuplicateByReference($product)) {
            return;
        }

        $this
            ->context
            ->buildViolation('ekyna_product.product.duplicate_reference', [
                '%designation%' => $duplicate->getFullDesignation(),
            ])
            ->atPath('reference')
            ->addViolation();
    }

    /**
     * Validates the product designation uniqueness.
     *
     * @param Model\ProductInterface $product
     */
    private function validateDesignation(Model\ProductInterface $product): void
    {
        $duplicate = $this
            ->repository
            ->findDuplicateByDesignationAndBrand($product, $product->getVariants()->toArray());

        if (null === $duplicate) {
            return;
        }

        $this
            ->context
            ->buildViolation('ekyna_product.product.duplicate_designation', [
                '%reference%' => $duplicate->getReference(),
            ])
            ->atPath('designation')
            ->addViolation();
    }

    /**
     * Validates the reference designation.
     *
     * @param Model\ProductInterface $product
     */
    private function validateVariantDesignation(Model\ProductInterface $product): void
    {
        if (empty($product->getDesignation())) {
            return;
        }

        foreach ($product->getParent()->getVariants() as $variant) {
            if ($variant === $product) {
                continue;
            }

            if ($variant->getDesignation() == $product->getDesignation()) {
                $this
                    ->context
                    ->buildViolation('ekyna_product.product.duplicate_variant_designation')
                    ->atPath('designation')
                    ->addViolation();
            }
        }
    }
}

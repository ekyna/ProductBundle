<?php

namespace Ekyna\Bundle\ProductBundle\Validator\Constraints;

use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class ProductValidator
 * @package Ekyna\Bundle\ProductBundle\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductValidator extends ConstraintValidator
{
    /**
     * @var ProductRepositoryInterface
     */
    private $repository;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $repository
     */
    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function validate($product, Constraint $constraint)
    {
        if (!$product instanceof Model\ProductInterface) {
            throw new InvalidArgumentException("Expected instance of " . Model\ProductInterface::class);
        }
        if (!$constraint instanceof Product) {
            throw new InvalidArgumentException("Expected instance of " . Product::class);
        }

        $this->validateReference($product);

        if ($product->getType() === Model\ProductTypes::TYPE_VARIANT) {
            $this->validateVariantDesignation($product);
        } else {
            $this->validateDesignation($product);
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

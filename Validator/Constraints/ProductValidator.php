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
    private $productRepository;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
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

        /* @var Model\ProductInterface $product */
        /* @var Product $constraint */

        if (!$product->isVisible()) {
            // Configurable product MUST be visible
            if ($product->getType() === Model\ProductTypes::TYPE_CONFIGURABLE) {
                $this->context
                    ->buildViolation($constraint->configurable_must_be_visible)
                    ->atPath('visible')
                    ->addViolation();

                return;
            }

            // Bundle product must be visible if it composed by at least one visible product
            if ($product->getType() === Model\ProductTypes::TYPE_BUNDLE) {
                foreach ($product->getBundleSlots() as $slot) {
                    /** @var Model\BundleChoiceInterface $choice */
                    $choice = $slot->getChoices()->first();
                    if ($choice->getProduct()->isVisible()) {
                        $this->context
                            ->buildViolation($constraint->bundle_must_be_visible)
                            ->atPath('visible')
                            ->addViolation();

                        return;
                    }
                }
            }
        }

        if ($product->getType() === Model\ProductTypes::TYPE_CONFIGURABLE) {
            return;
        }

        $parents = $this->productRepository->findParentsByBundled($product);

        if ($product->isVisible()) {
            // Visible products can't compose invisible products
            foreach ($parents as $parent) {
                if (!$parent->isVisible()) {
                    $this->context
                        ->buildViolation($constraint->child_must_not_be_visible)
                        ->atPath('visible')
                        ->addViolation();

                    return;
                }
            }
        } else {
            // Non visible product must have the same tax group as its parent's one
            foreach ($parents as $parent) {
                if ($product->getTaxGroup() !== $parent->getTaxGroup()) {
                    $this->context
                        ->buildViolation($constraint->parent_tax_group_integrity, [
                            '{{group}}' => (string)$parent->getTaxGroup(),
                        ])
                        ->atPath('taxGroup')
                        ->addViolation();

                    return;
                }
            }
        }
    }
}

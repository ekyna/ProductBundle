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

        // TODO unique option groups by name

        // TODO unique variants by designation (if not null)
    }
}

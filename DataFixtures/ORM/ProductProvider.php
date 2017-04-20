<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\DataFixtures\ORM;

use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Repository\ProductRepositoryInterface;

/**
 * Class ProductProvider
 * @package Ekyna\Bundle\ProductBundle\DataFixtures\ORM
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductProvider
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Returns the product by its reference.
     */
    public function getProduct(string $reference): ProductInterface
    {
        return $this->productRepository->findOneBy([
            'reference' => $reference,
        ]);
    }
}

<?php

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
     * Returns the product by its reference.
     *
     * @param string $reference
     *
     * @return ProductInterface
     */
    public function getProduct(string $reference): ProductInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->productRepository->findOneBy([
            'reference' => $reference,
        ]);
    }
}

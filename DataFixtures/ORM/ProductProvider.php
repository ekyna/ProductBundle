<?php

namespace Ekyna\Bundle\ProductBundle\DataFixtures\ORM;

use Ekyna\Bundle\MediaBundle\Entity\MediaRepository;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Entity\ProductMedia;
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
     * @var MediaRepository
     */
    private $mediaRepository;


    /**
     * Constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param MediaRepository $mediaRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository, MediaRepository $mediaRepository)
    {
        $this->productRepository = $productRepository;
        $this->mediaRepository = $mediaRepository;
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

    /**
     * Generates the product medias.
     *
     * @param ProductInterface $product
     */
    public function generateProductMedias(ProductInterface $product)
    {
        $images = $this->mediaRepository->findRandomBy(['type' => MediaTypes::IMAGE], rand(2, 4));

        foreach ($images as $image) {
            $product->addMedia((new ProductMedia())->setMedia($image));
        }
    }
}

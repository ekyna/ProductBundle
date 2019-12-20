<?php

namespace Ekyna\Bundle\ProductBundle\DataFixtures;

use Ekyna\Bundle\MediaBundle\Entity\MediaRepository;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\ProductBundle\Entity\ProductMedia;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Service\Generator\ExternalReferenceGenerator;
use Fidry\AliceDataFixtures\ProcessorInterface;

/**
 * Class ProductProcessor
 * @package Ekyna\Bundle\ProductBundle\DataFixtures
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductProcessor implements ProcessorInterface
{
    /**
     * @var ExternalReferenceGenerator
     */
    private $generator;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;


    /**
     * Constructor.
     *
     * @param ExternalReferenceGenerator $generator
     * @param MediaRepository            $mediaRepository
     */
    public function __construct(ExternalReferenceGenerator $generator, MediaRepository $mediaRepository)
    {
        $this->generator       = $generator;
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * @inheritDoc
     */
    public function preProcess(string $id, $object): void
    {
        if ($object instanceof ProductInterface) {
            $this->preProcessProduct($object);
        }
    }

    private function preProcessProduct(ProductInterface $product): void
    {
        if (!ProductTypes::isChildType($product)) {
            return;
        }

        $this->generator->generateGtin13($product);

        // TODO manufacturer code

        $this->generateProductMedias($product);
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

    /**
     * @inheritDoc
     */
    public function postProcess(string $id, $object): void
    {
        return;
    }
}

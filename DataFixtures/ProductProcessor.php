<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\DataFixtures;

use Ekyna\Bundle\MediaBundle\Model\MediaInterface;
use Ekyna\Bundle\MediaBundle\Model\MediaTypes;
use Ekyna\Bundle\MediaBundle\Repository\MediaRepositoryInterface;
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
    private ExternalReferenceGenerator $generator;
    private MediaRepositoryInterface $mediaRepository;

    public function __construct(ExternalReferenceGenerator $generator, MediaRepositoryInterface $mediaRepository)
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

        if (ProductTypes::isChildType($product) && $product->getPackageWeight()->isZero()) {
            $product->setPackageWeight($product->getWeight()->add('0.1'));
        }

        if ($product->getReference() !== 'HUB') {
            $this->generator->generateGtin13($product);
        }

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
        /** @var array<MediaInterface> $images */
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

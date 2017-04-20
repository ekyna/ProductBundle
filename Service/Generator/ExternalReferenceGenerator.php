<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Generator;

use Ekyna\Bundle\ProductBundle\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Model\ProductInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceInterface;
use Ekyna\Bundle\ProductBundle\Model\ProductReferenceTypes;
use Ekyna\Bundle\ProductBundle\Model\ProductTypes;
use Ekyna\Bundle\ProductBundle\Repository\ProductReferenceRepositoryInterface;
use Ekyna\Component\Resource\Factory\ResourceFactoryInterface;

/**
 * Class ExternalReferenceGenerator
 * @package Ekyna\Bundle\ProductBundle\Service\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExternalReferenceGenerator
{
    private GtinGeneratorInterface              $gtin13Generator;
    private ProductReferenceRepositoryInterface $repository;
    private ResourceFactoryInterface            $factory;

    public function __construct(
        ProductReferenceRepositoryInterface $repository,
        ResourceFactoryInterface            $factory
    ) {
        $this->repository = $repository;
        $this->factory = $factory;
    }

    /**
     * Sets the gtin13Generator.
     */
    public function setGtin13Generator(GtinGeneratorInterface $gtin13Generator)
    {
        $this->gtin13Generator = $gtin13Generator;
    }

    /**
     * Generates the product's Gtin13 reference.
     */
    public function generateGtin13(ProductInterface $product): void
    {
        if (!(ProductTypes::isChildType($product) || ProductTypes::isBundleType($product))) {
            throw new RuntimeException('Unexpected product type.');
        }

        if (null === $this->gtin13Generator) {
            throw new RuntimeException('Gtin13 generator is not enabled.');
        }

        if (null !== $product->getReferenceByType(ProductReferenceTypes::TYPE_EAN_13)) {
            throw new RuntimeException('Product already have a Gtin 13 reference.');
        }

        $count = 0;
        do {
            if (30 < $count) {
                throw new RuntimeException('Failed to generate Gtin 13 code.');
            }

            $code = $this->gtin13Generator->generate($product);

            $count++;
        } while (null !== $this->repository->findOneByTypeAndCode(ProductReferenceTypes::TYPE_EAN_13, $code));

        /** @var ProductReferenceInterface $reference */
        $reference = $this->factory->create();
        $reference
            ->setType(ProductReferenceTypes::TYPE_EAN_13)
            ->setCode($code);

        $product->addReference($reference);
    }
}

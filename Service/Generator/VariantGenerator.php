<?php

declare(strict_types=1);

namespace Ekyna\Bundle\ProductBundle\Service\Generator;

use Ekyna\Bundle\ProductBundle\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Factory\ProductFactoryInterface;
use Ekyna\Bundle\ProductBundle\Model;
use Ekyna\Component\Resource\Copier\CopierInterface;

/**
 * Class VariantGenerator
 * @package Ekyna\Bundle\ProductBundle\Service\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantGenerator implements VariantGeneratorInterface
{
    private ProductFactoryInterface $productFactory;
    private CopierInterface         $copier;

    public function __construct(ProductFactoryInterface $productFactory, CopierInterface $copier)
    {
        $this->productFactory = $productFactory;
        $this->copier = $copier;
    }

    public function generateVariants(Model\ProductInterface $variable): array
    {
        Model\ProductTypes::assertVariable($variable);

        if (null === $attributeSet = $variable->getAttributeSet()) {
            throw new RuntimeException('Variable attribute set must be defined.');
        }

        /** @var array<Model\ProductInterface> $variants */
        $variants = [];

        foreach ($attributeSet->getSlots() as $slot) {
            $attributes = $slot->getAttribute()->getChoices();

            // First pass : create initial variants
            if (empty($variants)) {
                foreach ($attributes as $attribute) {
                    $variants[] = $this
                        ->productFactory
                        ->createWithType(Model\ProductTypes::TYPE_VARIANT)
                        ->addAttribute($attribute);
                }

                continue;
            }

            $tmp = [];

            // Next passes : clone variants to preserve previous pass variants.
            foreach ($attributes as $attribute) {
                foreach ($variants as $variant) {
                    $clone = $this->copier->copyResource($variant);
                    $tmp[] = $clone->addAttribute($attribute);
                }
            }

            $variants = $tmp;
        }

        return $variants;
    }
}

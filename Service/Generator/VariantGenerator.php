<?php

namespace Ekyna\Bundle\ProductBundle\Service\Generator;

use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Bundle\ProductBundle\Model;

/**
 * Class VariantGenerator
 * @package Ekyna\Bundle\ProductBundle\Service\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantGenerator implements VariantGeneratorInterface
{
    /**
     * @var string
     */
    protected $productClass;


    /**
     * Constructor.
     *
     * @param string $productClass
     */
    public function __construct($productClass)
    {
        $this->productClass = $productClass;
    }

    /**
     * @inheritdoc
     */
    public function generateVariants(Model\ProductInterface $variable)
    {
        Model\ProductTypes::assertVariable($variable);

        if (null === $attributeSet = $variable->getAttributeSet()) {
            throw new RuntimeException("Variable attribute set must be defined.");
        }

        $variants = [];

        foreach ($attributeSet->getSlots() as $slot) {
            $attributes = $slot->getAttribute()->getChoices();

            // First pass : create initial variants
            if (empty($variants)) {
                foreach ($attributes as $attribute) {
                    /** @var Model\ProductInterface $variant */
                    $variant = new $this->productClass();
                    $variant
                        ->setType(Model\ProductTypes::TYPE_VARIANT)
                        ->addAttribute($attribute);

                    $variants[] = $variant;
                }
                continue;
            }

            $tmp = [];

            // Next passes : clone variants to preserve previous pass variants.
            foreach ($attributes as $attribute) {
                foreach ($variants as $variant) {
                    $clone = clone $variant;
                    $tmp[] = $clone->addAttribute($attribute);
                }
            }

            $variants = $tmp;
        }

        return $variants;
    }
}
